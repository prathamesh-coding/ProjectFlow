/**
 * controllers.js – All AngularJS Controllers
 * Rebranded: "Courses" → "Categories", supports Personal / Work / Freelance / Study / Other
 */
(function () {
  'use strict';

  // TYPE_META: icons and labels for category types
  var TYPE_META = {
    personal:  { icon: 'bi-house-heart', label: 'Personal',  css: 'type-personal'  },
    work:      { icon: 'bi-briefcase',   label: 'Work',      css: 'type-work'      },
    freelance: { icon: 'bi-laptop',      label: 'Freelance', css: 'type-freelance' },
    study:     { icon: 'bi-book',        label: 'Study',     css: 'type-study'     },
    other:     { icon: 'bi-grid',        label: 'Other',     css: 'type-other'     },
  };

  var PRESET_COLORS = [
    '#6366f1','#8b5cf6','#ec4899','#ef4444',
    '#f97316','#f59e0b','#10b981','#14b8a6',
    '#3b82f6','#06b6d4','#84cc16','#64748b',
  ];

  // ════════════════════════════════════════════════════════════════════════════
  // MainController – Global state, Quick Add, Categories Manager, Toasts
  // ════════════════════════════════════════════════════════════════════════════
  angular.module('studentPM')
    .controller('MainController', ['$scope', '$location', 'TaskService', 'CategoryService',
      function ($scope, $location, TaskService, CategoryService) {

        $scope.categories   = [];
        $scope.toasts       = [];
        $scope.isLoading    = false;
        $scope.typeMeta     = TYPE_META;
        $scope.presetColors = PRESET_COLORS;

        // Quick-add task model
        $scope.newTask = { title: '', category_id: '', priority: 'medium', due_date: '', status: 'todo' };

        // Category manager state
        $scope.catForm     = { name: '', color_code: '#6366f1', type: 'personal' };
        $scope.editingCat  = null;
        $scope.catErrors   = '';
        $scope.catSaving   = false;

        // Theme toggle (matches jira-styleguide.html)
        $scope.currentTheme = localStorage.getItem('jira-style-theme') || 'light';
        document.documentElement.dataset.theme = $scope.currentTheme;

        $scope.toggleTheme = function () {
          $scope.currentTheme = $scope.currentTheme === 'dark' ? 'light' : 'dark';
          document.documentElement.dataset.theme = $scope.currentTheme;
          localStorage.setItem('jira-style-theme', $scope.currentTheme);
        };

        // Active nav detection & breadcrumb
        $scope.isActive = function (path) { return $location.path() === path; };
        $scope.currentViewName = function () {
          var p = $location.path();
          if (p === '/dashboard') return 'Board';
          if (p === '/database') return 'Database';
          if (p.indexOf('/notes') === 0) return 'Notes';
          return 'Board';
        };

        // Type helpers
        $scope.typeIcon  = function (t) { return (TYPE_META[t] || TYPE_META.other).icon; };
        $scope.typeCss   = function (t) { return (TYPE_META[t] || TYPE_META.other).css; };
        $scope.typeLabel = function (t) { return (TYPE_META[t] || TYPE_META.other).label; };

        // Color swatch selection
        $scope.selectColor = function (hex) { $scope.catForm.color_code = hex; };
        $scope.isSelectedColor = function (hex) { return $scope.catForm.color_code === hex; };

        // Load categories
        $scope.loadCategories = function () {
          CategoryService.getCategories().then(function (res) {
            $scope.categories = res.data.courses || [];
          });
        };
        $scope.loadCategories();

        // Toast helper
        $scope.addToast = function (message, type) {
          var toast = { message: message, type: type || 'success', id: Date.now() };
          $scope.toasts.push(toast);
          setTimeout(function () {
            $scope.$apply(function () {
              $scope.toasts = $scope.toasts.filter(function (t) { return t.id !== toast.id; });
            });
          }, 3500);
        };

        // Quick-add modal submit
        $scope.quickAddTask = function () {
          if (!$scope.newTask.title.trim()) return;
          $scope.isLoading = true;
          // map category_id → course_id for API
          var payload = angular.copy($scope.newTask);
          payload.course_id = payload.category_id;
          delete payload.category_id;

          TaskService.createTask(payload).then(function (res) {
            if (res.data.success) {
              $scope.addToast('"' + res.data.task.title + '" added!', 'success');
              $scope.newTask = { title: '', category_id: '', priority: 'medium', due_date: '', status: 'todo' };
              var modal = bootstrap.Modal.getInstance(document.getElementById('quickAddModal'));
              if (modal) modal.hide();
              $scope.$broadcast('taskCreated', res.data.task);
            }
          }).catch(function () {
            $scope.addToast('Failed to create task.', 'danger');
          }).finally(function () { $scope.isLoading = false; });
        };

        // ── Category Manager ───────────────────────────────────────────────
        $scope.openNewCatForm = function () {
          $scope.editingCat = null;
          $scope.catForm = { name: '', color_code: '#6366f1', type: 'personal' };
          $scope.catErrors = '';
        };

        $scope.editCategory = function (cat) {
          $scope.editingCat = cat;
          $scope.catForm = { name: cat.name, color_code: cat.color_code, type: cat.type };
          $scope.catErrors = '';
        };

        $scope.saveCategoryForm = function () {
          $scope.catErrors = '';
          if (!$scope.catForm.name.trim()) { $scope.catErrors = 'Name is required.'; return; }
          $scope.catSaving = true;

          var promise;
          if ($scope.editingCat) {
            var payload = angular.copy($scope.catForm);
            payload.id = $scope.editingCat.id;
            promise = CategoryService.updateCategory(payload);
          } else {
            promise = CategoryService.createCategory($scope.catForm);
          }

          promise.then(function (res) {
            if (res.data.success) {
              $scope.loadCategories();
              $scope.$broadcast('categoriesUpdated');
              $scope.addToast($scope.editingCat ? 'Category updated!' : 'Category created!', 'success');
              $scope.openNewCatForm(); // reset form
            }
          }).catch(function () {
            $scope.catErrors = 'Failed to save category.';
          }).finally(function () { $scope.catSaving = false; });
        };

        $scope.deleteCategory = function (cat) {
          if (!confirm('Delete "' + cat.name + '"? Tasks in this category will become uncategorized.')) return;
          CategoryService.deleteCategory(cat.id).then(function (res) {
            if (res.data.success) {
              $scope.loadCategories();
              $scope.$broadcast('categoriesUpdated');
              $scope.addToast('"' + cat.name + '" deleted.', 'warning');
              if ($scope.editingCat && $scope.editingCat.id === cat.id) $scope.openNewCatForm();
            }
          }).catch(function () {
            $scope.addToast('Failed to delete category.', 'danger');
          });
        };

        // Group categories by type for display
        $scope.categoriesByType = function () {
          var groups = {};
          ($scope.categories || []).forEach(function (c) {
            var t = c.type || 'other';
            if (!groups[t]) groups[t] = [];
            groups[t].push(c);
          });
          return groups;
        };

        $scope.typeOrder = ['personal','work','freelance','study','other'];
      }
    ])

  // ════════════════════════════════════════════════════════════════════════════
  // DashboardController – Kanban Board
  // ════════════════════════════════════════════════════════════════════════════
    .controller('DashboardController', ['$scope', 'TaskService', 'CategoryService',
      function ($scope, TaskService, CategoryService) {

        $scope.columns = [
          { key: 'todo',        label: 'To Do',      icon: 'bi-circle',       colorClass: 'col-todo'        },
          { key: 'in_progress', label: 'In Progress', icon: 'bi-arrow-repeat', colorClass: 'col-inprogress'  },
          { key: 'blocked',     label: 'Blocked',     icon: 'bi-slash-circle', colorClass: 'col-blocked'     },
          { key: 'done',        label: 'Done',        icon: 'bi-check-circle', colorClass: 'col-done'        },
        ];

        $scope.allTasks      = [];
        $scope.categories    = [];
        $scope.todayOnly     = false;
        $scope.filterCat     = '';
        $scope.filterType    = '';
        $scope.loading       = true;
        $scope.dragTask      = null;

        var loadAll = function () {
          $scope.loading = true;
          var params = {};
          if ($scope.todayOnly)  params.today_only = 1;
          if ($scope.filterCat)  params.course_id  = $scope.filterCat;
          TaskService.getTasks(params).then(function (res) {
            $scope.allTasks = res.data.tasks || [];
          }).finally(function () { $scope.loading = false; });
        };

        CategoryService.getCategories().then(function (r) { $scope.categories = r.data.courses || []; });
        loadAll();

        $scope.getColumnTasks = function (key) {
          return $scope.allTasks.filter(function (t) {
            return t.status === key && (!$scope.filterType || t.course_type === $scope.filterType);
          });
        };

        // Drag-and-drop
        $scope.onDragStart = function (task) { $scope.dragTask = task; };

        $scope.onDrop = function ($event, targetStatus) {
          $event.preventDefault();
          if (!$scope.dragTask || $scope.dragTask.status === targetStatus) return;
          var task = $scope.dragTask, oldSt = task.status;
          task.status = targetStatus;
          TaskService.updateTask({ id: task.id, status: targetStatus }).then(function (res) {
            if (!res.data.success) { task.status = oldSt; }
            else { $scope.$parent.addToast('"' + task.title + '" moved to ' + targetStatus.replace('_', ' ') + '.', 'success'); }
          }).catch(function () { task.status = oldSt; });
          $scope.dragTask = null;
        };

        $scope.deleteTask = function (task) {
          if (!confirm('Delete "' + task.title + '"?')) return;
          TaskService.deleteTask(task.id).then(function (res) {
            if (res.data.success) {
              $scope.allTasks = $scope.allTasks.filter(function (t) { return t.id !== task.id; });
              $scope.$parent.addToast('Task deleted.', 'warning');
            }
          });
        };

        $scope.priorityBadge = function (p) {
          return { high: 'badge-priority-high', medium: 'badge-priority-medium', low: 'badge-priority-low' }[p] || '';
        };

        $scope.$on('taskCreated',        function (e, t)  { $scope.allTasks.push(t); });
        $scope.$on('categoriesUpdated',  function ()      { CategoryService.getCategories().then(function (r) { $scope.categories = r.data.courses || []; }); });
        $scope.$watch('todayOnly',   function () { loadAll(); });
        $scope.$watch('filterCat',   function () { loadAll(); });
      }
    ])

  // ════════════════════════════════════════════════════════════════════════════
  // DatabaseController – Spreadsheet View
  // ════════════════════════════════════════════════════════════════════════════
    .controller('DatabaseController', ['$scope', '$timeout', 'TaskService', 'CategoryService',
      function ($scope, $timeout, TaskService, CategoryService) {

        $scope.tasks        = [];
        $scope.categories   = [];
        $scope.loading      = true;
        $scope.sortField    = 'due_date';
        $scope.sortReverse  = false;
        $scope.filterStatus = '';
        $scope.filterCat    = '';
        $scope.filterType   = '';
        $scope.searchQuery  = '';
        $scope.saveTimers   = {};
        $scope.savingId     = null;
        $scope.savedId      = null;
        $scope.newRow       = null;
        $scope.statuses     = ['todo', 'in_progress', 'blocked', 'done'];
        $scope.priorities   = ['low', 'medium', 'high'];
        $scope.types        = ['personal', 'work', 'freelance', 'study', 'other'];

        var loadAll = function () {
          $scope.loading = true;
          CategoryService.getCategories().then(function (r) { $scope.categories = r.data.courses || []; });
          TaskService.getTasks().then(function (res) {
            $scope.tasks = res.data.tasks || [];
          }).finally(function () { $scope.loading = false; });
        };
        loadAll();

        $scope.setSort = function (field) {
          if ($scope.sortField === field) $scope.sortReverse = !$scope.sortReverse;
          else { $scope.sortField = field; $scope.sortReverse = false; }
        };

        $scope.sortIcon = function (field) {
          if ($scope.sortField !== field) return 'bi-chevron-expand';
          return $scope.sortReverse ? 'bi-chevron-up' : 'bi-chevron-down';
        };

        $scope.onCellChange = function (task, field) {
          if ($scope.saveTimers[task.id]) $timeout.cancel($scope.saveTimers[task.id]);
          $scope.savingId = task.id;
          $scope.saveTimers[task.id] = $timeout(function () {
            var payload = { id: task.id };
            payload[field] = task[field];
            // map category_id to course_id
            if (field === 'category_id') { payload.course_id = task[field]; delete payload.category_id; }
            TaskService.updateTask(payload).then(function () {
              $scope.savingId = null;
              $scope.savedId  = task.id;
              $timeout(function () { $scope.savedId = null; }, 2000);
            });
          }, 800);
        };

        $scope.deleteTask = function (task) {
          if (!confirm('Delete "' + task.title + '"?')) return;
          TaskService.deleteTask(task.id).then(function (res) {
            if (res.data.success) {
              $scope.tasks = $scope.tasks.filter(function (t) { return t.id !== task.id; });
              $scope.$parent.addToast('Task deleted.', 'warning');
            }
          });
        };

        $scope.addNewRow = function () {
          $scope.newRow = { title: '', course_id: '', status: 'todo', priority: 'medium', due_date: '' };
        };

        $scope.saveNewRow = function () {
          if (!$scope.newRow || !$scope.newRow.title.trim()) return;
          TaskService.createTask($scope.newRow).then(function (res) {
            if (res.data.success) {
              $scope.tasks.unshift(res.data.task);
              $scope.newRow = null;
              $scope.$parent.addToast('Task added!', 'success');
            }
          });
        };

        $scope.cancelNewRow = function () { $scope.newRow = null; };

        $scope.getCategoryById = function (id) {
          return ($scope.categories || []).find(function (c) { return c.id == id; });
        };

        $scope.$on('taskCreated',       function (e, t) { $scope.tasks.unshift(t); });
        $scope.$on('categoriesUpdated', function ()     { CategoryService.getCategories().then(function (r) { $scope.categories = r.data.courses || []; }); });
      }
    ])

  // ════════════════════════════════════════════════════════════════════════════
  // NotesController – Task Canvas
  // ════════════════════════════════════════════════════════════════════════════
    .controller('NotesController', ['$scope', '$timeout', '$routeParams', 'TaskService', 'CategoryService',
      function ($scope, $timeout, $routeParams, TaskService, CategoryService) {

        $scope.tasks       = [];
        $scope.categories  = [];
        $scope.activeTask  = null;
        $scope.loading     = true;
        $scope.saveStatus  = '';
        $scope.searchQuery = '';
        $scope.statuses    = ['todo', 'in_progress', 'blocked', 'done'];
        $scope.priorities  = ['low', 'medium', 'high'];
        var saveTimer      = null;

        CategoryService.getCategories().then(function (r) { $scope.categories = r.data.courses || []; });

        TaskService.getTasks().then(function (res) {
          $scope.tasks = res.data.tasks || [];
          if ($routeParams.id) {
            var t = $scope.tasks.find(function (x) { return x.id == $routeParams.id; });
            if (t) $scope.openTask(t);
          } else if ($scope.tasks.length > 0) {
            $scope.openTask($scope.tasks[0]);
          }
        }).finally(function () { $scope.loading = false; });

        $scope.openTask = function (task) {
          $scope.activeTask = angular.copy(task);
          $scope.saveStatus = '';
          $timeout(function () {
            var el = document.getElementById('notesEditor');
            if (el && $scope.activeTask) el.innerHTML = $scope.activeTask.notes_body || '';
          }, 50);
        };

        $scope.onNotesChange = function () {
          if (!$scope.activeTask) return;
          $scope.saveStatus = 'saving';
          if (saveTimer) $timeout.cancel(saveTimer);
          saveTimer = $timeout(function () {
            TaskService.updateTask({ id: $scope.activeTask.id, notes_body: $scope.activeTask.notes_body })
              .then(function () {
                var idx = $scope.tasks.findIndex(function (t) { return t.id === $scope.activeTask.id; });
                if (idx > -1) $scope.tasks[idx].notes_body = $scope.activeTask.notes_body;
                $scope.saveStatus = 'saved';
                $timeout(function () { $scope.saveStatus = ''; }, 2000);
              }).catch(function () { $scope.saveStatus = ''; });
          }, 1000);
        };

        $scope.onMetaChange = function (field) {
          if (!$scope.activeTask) return;
          var payload = { id: $scope.activeTask.id };
          // map category_id → course_id
          if (field === 'category_id') {
            payload.course_id = $scope.activeTask.category_id;
          } else {
            payload[field] = $scope.activeTask[field];
          }
          TaskService.updateTask(payload).then(function (res) {
            if (res.data.success) {
              var idx = $scope.tasks.findIndex(function (t) { return t.id === $scope.activeTask.id; });
              if (idx > -1) angular.extend($scope.tasks[idx], res.data.task);
              $scope.$parent.addToast('Saved!', 'success');
            }
          });
        };

        $scope.deleteActiveTask = function () {
          if (!$scope.activeTask) return;
          if (!confirm('Delete "' + $scope.activeTask.title + '"?')) return;
          TaskService.deleteTask($scope.activeTask.id).then(function (res) {
            if (res.data.success) {
              $scope.tasks = $scope.tasks.filter(function (t) { return t.id !== $scope.activeTask.id; });
              $scope.activeTask = $scope.tasks.length ? $scope.tasks[0] : null;
              $scope.$parent.addToast('Task deleted.', 'warning');
            }
          });
        };

        $scope.getCategoryById = function (id) {
          return ($scope.categories || []).find(function (c) { return c.id == id; });
        };

        $scope.execFormat = function (cmd, value) {
          document.execCommand(cmd, false, value || null);
          var el = document.getElementById('notesEditor');
          if (el) { $scope.activeTask.notes_body = el.innerHTML; $scope.onNotesChange(); }
        };

        $scope.$on('taskCreated',       function (e, t) { $scope.tasks.unshift(t); });
        $scope.$on('categoriesUpdated', function ()     { CategoryService.getCategories().then(function (r) { $scope.categories = r.data.courses || []; }); });
      }
    ]);

})();
