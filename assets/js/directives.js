/**
 * directives.js - Custom AngularJS Directives
 */
(function () {
  'use strict';

  angular.module('studentPM')

    // ── Drag-and-drop: Draggable task card ──────────────────────────────────
    .directive('draggable', function () {
      return {
        restrict: 'A',
        link: function (scope, element, attrs) {
          element[0].draggable = true;
          element[0].addEventListener('dragstart', function (e) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');
            element.addClass('dragging');
            // task is available as `task` on the ng-repeat scope
            scope.$apply(function () {
              var task = attrs.draggable ? scope.$eval(attrs.draggable) : scope.task;
              scope.onDragStart(task);
            });
          });
          element[0].addEventListener('dragend', function () {
            element.removeClass('dragging');
          });
        }
      };
    })

    // ── Drag-and-drop: Drop zone column ─────────────────────────────────────
    .directive('dropzone', function () {
      return {
        restrict: 'A',
        link: function (scope, element, attrs) {
          element[0].addEventListener('dragover', function (e) {
            e.preventDefault();
            element.addClass('drag-over');
          });
          element[0].addEventListener('dragleave', function () {
            element.removeClass('drag-over');
          });
          element[0].addEventListener('drop', function (e) {
            e.preventDefault();
            element.removeClass('drag-over');
            scope.$apply(function () {
              scope.onDrop(e, attrs.dropzone);
            });
          });
        }
      };
    })

    // ── Auto-resize textarea ─────────────────────────────────────────────────
    .directive('autoResize', function () {
      return {
        restrict: 'A',
        link: function (scope, element) {
          function resize() {
            element[0].style.height = 'auto';
            element[0].style.height = element[0].scrollHeight + 'px';
          }
          element[0].addEventListener('input', resize);
          setTimeout(resize, 100);
        }
      };
    })

    // ── Sync contenteditable <div> with ng-model ─────────────────────────────
    .directive('contenteditable', ['$sce', function ($sce) {
      return {
        restrict: 'A',
        require:  '?ngModel',
        link: function (scope, element, attrs, ngModel) {
          if (!ngModel) return;

          ngModel.$render = function () {
            element.html($sce.getTrustedHtml(ngModel.$viewValue || ''));
          };

          element.on('input blur', function () {
            scope.$apply(function () {
              ngModel.$setViewValue(element.html());
            });
          });

          // Prevent paste from injecting external styles
          element[0].addEventListener('paste', function (e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
          });
        }
      };
    }]);

})();
