import React, { useRef } from "react";
import ReactDOM from "react-dom";
import Header from "../../components/Header";
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
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import { Link } from "react-router-dom";
// import exports from "enhanced-resolve";

export class DefaultPage extends React.Component {

  constructor() {
    super();
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

        <p>
          Default page
        </p>
      </>
    );
  }

}

// ReactDOM.render(<App />, document.getElementById('scds-react-root'));
export default DefaultPage;
