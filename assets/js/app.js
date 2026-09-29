/**
 * app.js – AngularJS Module, Routes & Global Filters
 * ProjectFlow – Universal Project & Task Manager
 */
(function () {
  'use strict';

  var app = angular.module('studentPM', ['ngRoute']);

  // ─── Route Configuration ──────────────────────────────────────────────────
  app.config(['$routeProvider', function ($routeProvider) {
    $routeProvider
      .when('/dashboard', { templateUrl: 'views/dashboard.html', controller: 'DashboardController' })
      .when('/database',  { templateUrl: 'views/database.html',  controller: 'DatabaseController'  })
      .when('/notes',     { templateUrl: 'views/notes.html',     controller: 'NotesController'     })
      .when('/notes/:id', { templateUrl: 'views/notes.html',     controller: 'NotesController'     })
      .otherwise({ redirectTo: '/dashboard' });
  }]);

  // ─── Countdown Filter ─────────────────────────────────────────────────────
  app.filter('countdown', function () {
    return function (dueDateStr) {
      if (!dueDateStr) return 'No due date';
      var now = new Date(), due = new Date(dueDateStr);
      var diffMs = due - now;
      var diffH  = diffMs / (1000 * 60 * 60);
      var diffD  = diffMs / (1000 * 60 * 60 * 24);
      if (diffMs < 0) {
        if (Math.abs(diffH) < 24) return 'Overdue ' + Math.round(Math.abs(diffH)) + 'h ago';
        return 'Overdue ' + Math.abs(Math.ceil(diffD)) + 'd ago';
      }
      if (diffH < 1)  return 'Due in < 1h';
      if (diffH < 24) return 'Due in ' + Math.round(diffH) + 'h';
      if (diffD < 2)  return 'Due tomorrow';
      return 'Due in ' + Math.ceil(diffD) + ' days';
    };
  });

  // ─── Countdown CSS Class Filter ───────────────────────────────────────────
  app.filter('countdownClass', function () {
    return function (dueDateStr, status) {
      if (!dueDateStr || status === 'done') return 'text-muted';
      var diffH = (new Date(dueDateStr) - new Date()) / (1000 * 60 * 60);
      if (diffH < 0)   return 'text-danger fw-bold';
      if (diffH < 24)  return 'text-danger';
      if (diffH < 48)  return 'text-warning';
      return 'text-success';
    };
  });

  // ─── Safe HTML Filter ────────────────────────────────────────────────────
  app.filter('trusted', ['$sce', function ($sce) {
    return function (html) { return $sce.trustAsHtml(html || ''); };
  }]);

  // ─── Short Date Filter ────────────────────────────────────────────────────
  app.filter('shortDate', function () {
    return function (dateStr) {
      if (!dateStr) return '—';
      return new Date(dateStr).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    };
  });

  // ─── String Replace Filter ────────────────────────────────────────────────
  app.filter('replace', function () {
    return function (str, from, to) {
      if (!str) return '';
      return str.split(from).join(to || '');
    };
  });

  // ─── Title Case Filter ────────────────────────────────────────────────────
  app.filter('titlecase', function () {
    return function (str) {
      if (!str) return '';
      return str.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
      });
    };
  });

  // ─── Category Type Label Filter ───────────────────────────────────────────
  app.filter('typeLabel', function () {
    var map = { personal: 'Personal', work: 'Work', freelance: 'Freelance', study: 'Study', other: 'Other' };
    return function (t) { return map[t] || t; };
  });

})();
