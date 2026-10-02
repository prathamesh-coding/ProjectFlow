/**
 * services.js – AngularJS $http Service Layer
 */
(function () {
  'use strict';

  angular.module('studentPM')

    // ── Task Service ────────────────────────────────────────────────────────
    .service('TaskService', ['$http', function ($http) {
      var base = 'api/';

      this.getTasks = function (params) {
        return $http.get(base + 'get_tasks.php', { params: params || {} });
      };

      this.createTask = function (data) {
        return $http.post(base + 'create_task.php', data);
      };

      this.updateTask = function (data) {
        return $http.post(base + 'update_task.php', data);
      };

      this.deleteTask = function (id) {
        return $http.post(base + 'delete_task.php', { id: id });
      };
    }])

    // ── Category Service ────────────────────────────────────────────────────
    .service('CategoryService', ['$http', function ($http) {
      var base = 'api/';

      this.getCategories = function () {
        return $http.get(base + 'get_courses.php');
      };

      this.createCategory = function (data) {
        return $http.post(base + 'create_course.php', data);
      };

      this.updateCategory = function (data) {
        return $http.post(base + 'update_course.php', data);
      };

      this.deleteCategory = function (id) {
        return $http.post(base + 'delete_course.php', { id: id });
      };
    }])

    // ── Auth Service ────────────────────────────────────────────────────────
    .service('AuthService', ['$http', function ($http) {
      var base = 'api/';

      this.login = function (credentials) {
        return $http.post(base + 'login.php', credentials);
      };

      this.register = function (data) {
        return $http.post(base + 'register.php', data);
      };

      this.logout = function () {
        return $http.post(base + 'logout.php');
      };

      this.checkAuth = function () {
        return $http.get(base + 'check_auth.php');
      };

      this.getUsers = function () {
        return $http.get(base + 'get_users.php');
      };
    }]);

})();
