// import reducer from "../reducers/index";
// import { getState } from 'redux';
import store from "../reducers/store";

export function getKey(key) {
  const state = store.getState();
  return state["SKIPCLEAR/GlobalSettings"][key];
}

export function getName() {
  return getDetail("name");
}

export function getId() {
  return getDetail("id");
}

export function getUuid() {
  return getDetail("uuid");
}

export function getWebsite() {
  return getDetail("website");
}

export function getHostname() {
  return getDetail("hostname");
}

export function getCode() {
  return getDetail("swim_england_code");
}

export function isVerified() {
  return getDetail("is_verified");
}

export function getLogoUrl(filename) {
  return getDetail("club_logo_path") + filename;
}

function getDetail(key) {
  const state = store.getState();
  return state["SKIPCLEAR/Tenant"][key];
}

export function setTitle(title) {
  document.title = title + " - " + getKey("club_name");
}