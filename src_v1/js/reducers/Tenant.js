const Tenant = (state = {}, action) => {
  switch (action.type) {
  case "ADD_TENANT_DETAIL":
    return {
      ...state,
      [action.key]: action.value
    };
  case "ADD_TENANT_DETAILS":
    return {
      ...state,
      ...action.payload,
    };
  case "UPDATE_TENANT_DETAILS":
    return {
      ...state,
      [action.key]: action.value
    };
  default:
    return state;
  }
};

export default Tenant;
