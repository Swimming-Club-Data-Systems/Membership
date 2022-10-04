const User = (state = {}, action) => {
  switch (action.type) {
  case "ADD_USER_DETAIL":
    return {
      ...state,
      [action.key]: action.value
    };
  case "ADD_USER_DETAILS":
    return {
      ...state,
      ...action.payload,
    };
  case "UPDATE_USER_DETAILS":
    return {
      ...state,
      [action.key]: action.value
    };
  default:
    return state;
  }
};

export default User;
