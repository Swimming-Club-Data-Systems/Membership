// import reducer from "../reducers/index";
// import { getState } from 'redux';
import store from "../reducers/store";

export function getKey(key) {
  const state = store.getState();
  return state["SKIPCLEAR/UserSettings"][key];
}

export function getId() {
  return getDetail("id");
}

export function getFirstName() {
  return getDetail("first_name");
}

export function getLastName() {
  return getDetail("last_name");
}

export function getEmail() {
  return getDetail("email");
}

export function getMobile() {
  return getDetail("mobile");
}

export function getName() {
  return getFirstName() + " " + getLastName();
}

export function hasPermission(permission) {
  const permissions = getDetail("permissions");
  if (permissions) {
    return permissions.includes(permission);
  }
  return false;
}

export function hasPermissions(permissionsToCheck) {
  const permissions = getDetail("permissions");
  if (permissions) {
    return permissionsToCheck.some(r => permissions.includes(r));
  }
  return false;
}

function getDetail(key) {
  const state = store.getState();
  return state["SKIPCLEAR/User"][key];
}

export function setTitle(title) {
  document.title = title + " - " + getKey("club_name");
}