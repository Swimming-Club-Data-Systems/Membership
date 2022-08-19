import React from "react";
import { FloatingLabel, Form } from "react-bootstrap";


class DropdownRow extends React.Component {

  render() {
    const options = this.props.options.map((data, idx) => {
      return (
        <option key={idx} value={data.value}>{data.name}</option>
      );
    });

    return (
      <div className="my-2">
        <FloatingLabel controlId="floatingSelect" label={this.props.label}>
          <Form.Select aria-label={this.props.label} value={this.props.formValue} onChange={this.props.handleChange} name={this.props.name}>
            {options}
          </Form.Select>
        </FloatingLabel>
      </div>
    );
  }
}

export default DropdownRow;