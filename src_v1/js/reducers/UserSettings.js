const UserSettings = (state = {}, action) => {
  switch (action.type) {
  case "ADD_USER_KEY":
    return {
      ...state,
      [action.key]: action.value
    };
  case "ADD_USER_KEYS":
    return {
      ...state,
      ...action.payload,
    };
  case "UPDATE_USER_KEY":
    return {
      ...state,
      [action.key]: action.value
    };
  default:
    return state;
  }
};

export default UserSettings;
