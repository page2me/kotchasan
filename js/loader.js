/*
 * GLoader
 * Javascript page load (Ajax)
 *
 * @filesource js/table.js
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
(function () {
  'use strict';
  window.GLoader = GClass.create();
  GLoader.prototype = {
    initialize: function (reader, geturl, callback) {
      this.myhistory = new Array();
      this.geturl = geturl;
      this.callback = callback;
      this.req = new GAjax();
      var my_location = location.toString();
      var a = my_location.indexOf('?');
      var b = my_location.indexOf('#');
      var locs = my_location.split(/[\?\#]/);
      if (a > -1 && b > -1) {
        this.lasturl = a < b ? locs[1] : locs[2];
      } else if (a > -1) {
        this.lasturl = locs[1];
      } else {
        this.lasturl = '';
      }
      var temp = this;
      window.setInterval(function () {
        var curr_url = location.toString().replace('_=_', '');
        if (curr_url.indexOf('#') > -1) {
          locs = curr_url.split('#');
        } else {
          locs = curr_url.split('?');
        }
        locs = locs[1] && locs[1].indexOf('=') > -1 ? locs[1] : '';
        if (temp.lasturl != '' && locs == '') {
          var qs = temp.geturl.call(temp, curr_url);
          locs = qs === null ? 'module=' + FIRST_MODULE : qs.join('&');
        }
        if (locs !== '' && locs != temp.lasturl) {
          temp.lasturl = locs;
          temp.myhistory.push(locs);
          if (temp.myhistory.length > 2) {
            temp.myhistory.shift();
          }
          temp.req.send(reader, locs, callback);
        }
      }, 100);
    },
    initLoading: function (loading, center) {
      this.req.initLoading(loading, center);
      return this;
    },
    init: function (obj) {
      var temp = this;
      var patt1 = new RegExp('^.*' + location.hostname + '/(.*?)$');
      var patt2 = new RegExp('.*#.*?');
      forEach($E(obj).getElementsByTagName('a'), function () {
        if (this.target == '' && this.onclick == null && this.href != '' && patt1.exec(this.href) && !patt2.exec(this.href)) {
          this.onclick = function (e) {
            var evt = e || window.event;
            if (!(evt.shiftKey || evt.ctrlKey || evt.metaKey || evt.altKey)) {
              return temp.location(this.href);
            }
          };
        }
      });
      return this;
    },
    location: function (url) {
      var locs = window.location.toString().split('#'),
        ret = this.geturl.call(this, url);
      if (ret) {
        ret.push(new Date().getTime());
        window.location = locs[0] + '#' + decodeURIComponent(ret.join('&'));
        return false;
      } else {
        window.location = url;
      }
      return true;
    },
    back: function () {
      if (this.myhistory.length >= 2) {
        window.location = WEB_URL + 'index.php?' + this.myhistory[this.myhistory.length - 2];
      } else {
        window.history.go(-1);
      }
    },
    reload: function () {
      var locs = window.location.toString().split('#'),
        ret = new Array();
      if (locs.length > 1) {
        forEach(locs[1].split('&'), function () {
          if (!/[0-9]+/.test(this)) {
            ret.push(this);
          }
        });
      }
      ret.push(new Date().getTime());
      window.location = locs[0] + '#' + decodeURIComponent(ret.join('&'));
    }
  };
}());