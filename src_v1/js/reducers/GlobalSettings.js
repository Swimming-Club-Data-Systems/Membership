const GlobalSettings = (state = {}, action) => {
  switch (action.type) {
  case "ADD_TENANT_KEY":
    return {
      ...state,
      [action.key]: action.value
    };
  case "ADD_TENANT_KEYS":
    return {
      ...state,
      ...action.payload,
    };
  case "UPDATE_TENANT_KEY":
    return {
      ...state,
      [action.key]: action.value
    };
  default:
    return state;
  }
};

export default GlobalSettings;
