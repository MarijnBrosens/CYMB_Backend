import Vue from "vue";
import I18n from "vuex-i18n";
import store from "./store.js";

Vue.use(I18n.plugin, store);

// translate filter for Ui components
Vue.filter("t", function(fallback, key) {
  return key ? Vue.i18n.translate(key, fallback) : fallback;
});
