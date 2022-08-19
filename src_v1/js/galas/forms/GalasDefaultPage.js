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

class GalasDefaultPage extends React.Component {

  constructor(props) {
    super(props);
  }

  render() {
    const breadcrumbs = (
      <Breadcrumb>
        <Breadcrumb.Item href="/galas">Galas</Breadcrumb.Item>
        <Breadcrumb.Item active>V2</Breadcrumb.Item>
      </Breadcrumb>
    );

    return (
      <>
        <Header title="Galas V2 Default Page" subtitle="Custom events for galas" breadcrumbs={breadcrumbs} />

        <Container fluid="lg">
          <Row>
            <Col>1 of 1</Col>
          </Row>
        </Container>
      </>
    );
  }

}

const mapStateToProps = (state) => ({
  "global_settings": state["SKIPCLEAR/GlobalSettings"],
});

export default connect(mapStateToProps)(GalasDefaultPage);
