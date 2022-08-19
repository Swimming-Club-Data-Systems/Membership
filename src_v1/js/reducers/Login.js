const Login = (state = {}, action) => {
  switch (action.type) {
  case "SET_LOGIN_STATE":
    return {
      ...state,
      [action.key]: action.value
    };
  case "WRITE_LOGIN_STATE":
    return {
      ...state,
      ...action.data
    };
  default:
    return state;
  }
};

export const mapStateToProps = (state) => {
  const { Login } = state;
  return {
    ...Login,
    login_page_type: Login.login_page_type || "login",
  };
};

export const mapDispatchToProps = (dispatch) => {
  return {
    setType: (type) => dispatch(
      {
        type: "SET_LOGIN_STATE",
        key: "login_page_type",
        value: type,
      }),
    setLoginDetail: (key, value) => dispatch(
      {
        type: "SET_LOGIN_STATE",
        key: key,
        value: value,
      }),
    setLoginDetails: (object) => dispatch(
      {
        type: "WRITE_LOGIN_STATE",
        data: object,
      }),
  };
};

export default Login;
