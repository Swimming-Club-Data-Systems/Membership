import React from "react";
import { Form } from "react-bootstrap";


class Dropdown extends React.Component {

  render() {
    const options = this.props.options.map((data, idx) => {
      return (
        <option key={idx} value={data.value}>{data.name}</option>
      );
    });

    return (
      <Form.Group className="mb-3" controlId={this.props.name}>
        <Form.Label>{this.props.label}</Form.Label>
        <Form.Select aria-label={this.props.label} value={this.props.formValue} onChange={this.props.handleChange} name={this.props.name}>
          {options}
        </Form.Select>
      </Form.Group>
    );
  }
}

export default Dropdown;