import React from "react";
import { FloatingLabel, Form } from "react-bootstrap";


class DropdownRow extends React.Component {

  render() {
    let type = this.props.type ? this.props.type : "text";

    return (
      <div className="my-2">
        <FloatingLabel
          controlId="floatingInput"
          label={this.props.label}
          className="mb-3"
        >
          <Form.Control type={type} value={this.props.formValue} onChange={this.props.handleChange} name={this.props.name} required={this.props.required}/>
        </FloatingLabel>
      </div>
    );
  }
}

export default DropdownRow;