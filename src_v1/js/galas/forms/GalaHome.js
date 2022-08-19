import React, { useRef } from "react";
import ReactDOM from "react-dom";
import Header from "../../components/Header.js";
import Breadcrumb from "react-bootstrap/Breadcrumb";
import Modal from "react-bootstrap/Modal";
import Button from "react-bootstrap/Button";
import Tabs from "react-bootstrap/Tabs";
import Tab from "react-bootstrap/Tab";
import Form from "react-bootstrap/Form";
import Placeholder from "react-bootstrap/Placeholder";
import { Editor } from "@tinymce/tinymce-react";
import axios from "axios";
import Accordion from "react-bootstrap/Accordion";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import { Link } from "react-router-dom";
import { connect } from "react-redux";
// import exports from "enhanced-resolve";

class GalaHomePage extends React.Component {

  constructor(props) {
    super(props);
  }

  render() {
    const breadcrumbs = (
      <Breadcrumb>
        <Breadcrumb.Item active>Galas</Breadcrumb.Item>
      </Breadcrumb>
    );

    return (
      <>
        <Header title="Galas" subtitle={`Galas at ${this.props.global_settings.club_name}`} breadcrumbs={breadcrumbs} />

        <Container fluid="lg">

          <Link to="enter-gala">Enter Gala</Link>

          <Row>
            <Col>1 of 1</Col>
          </Row>
        </Container>
      </>
    );
  }

}

// ReactDOM.render(<App />, document.getElementById('scds-react-root'));
const mapStateToProps = (state) => ({
  "global_settings": state["SKIPCLEAR/GlobalSettings"],
});

export default connect(mapStateToProps)(GalaHomePage);
