export const MainStore = (state = {}, action) => {
  switch (action.type) {
    case "SET_STATE":
      return {
        ...state,
        [action.key]: action.value
      };
    case "SET_STATE_OBJECT":
      return {
        ...state,
        ...action.data
      };
    case "CLEAR":
      return {};
    default:
      return state;
  }
};

export const mapDispatchToProps = (dispatch) => {
  return {
    setValue: (key, value) => dispatch(
      {
        type: "SET_STATE",
        key: key,
        value: value,
      }),
    setValues: (object) => dispatch(
      {
        type: "SET_STATE_OBJECT",
        data: object,
      }),
    clearRedux: () => dispatch(
      {
        type: "CLEAR",
      }),
  };
};

export default MainStore;