import { useEffect } from "react";
import { connect } from "react-redux";
import { useLocation } from "react-router-dom";
import { mapDispatchToProps } from "../../reducers/MainStore";

function ScrollToTop(props) {

  const location = useLocation();

  useEffect(() => {
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: "auto"
    });
    props.clearRedux();
  }, [location]);

  return (null);
}

export default connect(null, mapDispatchToProps)(ScrollToTop);