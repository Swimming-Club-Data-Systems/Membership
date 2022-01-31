import { combineReducers } from "redux";
import GlobalSettings from "./GlobalSettings";
import Tenant from "./Tenant";
import User from "./User";
import UserSettings from "./UserSettings";

export default combineReducers({
  "SKIPCLEAR/GlobalSettings": GlobalSettings,
  "SKIPCLEAR/Tenant": Tenant,
  "SKIPCLEAR/User": User,
  "SKIPCLEAR/UserSettings": UserSettings,
});