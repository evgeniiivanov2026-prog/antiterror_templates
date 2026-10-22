(function (wp) {
  if (!wp || !wp.customize) return;
  wp.customize.bind("ready", function () {
    document.querySelectorAll(".rubezh-order").forEach(function (list) {
      var setting = wp.customize(list.getAttribute("data-setting"));
      if (!setting) return;
      var commit = function () {
        var keys = Array.prototype.map.call(list.querySelectorAll("[data-key]"), function (item) {
          return item.getAttribute("data-key");
        });
        setting.set(keys.join(","));
      };
      list.addEventListener("click", function (event) {
        var button = event.target.closest("button");
        if (!button) return;
        var item = button.closest("[data-key]");
        if (!item) return;
        if (button.classList.contains("is-up") && item.previousElementSibling) {
          item.parentNode.insertBefore(item, item.previousElementSibling);
          commit();
        }
        if (button.classList.contains("is-down") && item.nextElementSibling) {
          item.parentNode.insertBefore(item.nextElementSibling, item);
          commit();
        }
      });
    });
  });
}(window.wp));
