import React from "react";
import Pill from "./Pill";
import Button from "react-bootstrap/Button";

class ToRow extends React.Component {

  render() {
    const pills = this.props.groups.map(data => {
      if (data.state) {
        return (
          <Pill key={data.key} data={data} handleClick={this.props.handleClick} />
        );
      }
    });

    let classes = ["form-control", "h-100"];
    if (this.props.validated && this.props.numTos === 0) {
      classes.push("is-invalid");
    } else if (this.props.validated) {
      classes.push("is-valid");
    }
    let controlClassNames = classes.join(" ");

    return (
      <div className="my-2 form-floating">
        <div className={controlClassNames} id="tosInput">
          <div className="row">
            <div className="col-auto">
              <Button variant="primary" onClick={this.props.handleShowTo}>
                To
              </Button>
            </div>
            <div className="col p-1 pb-0">
              {pills}
            </div>
          </div>
        </div>
        <label>Recipients</label>
      </div>
    );
  }
}

export default ToRow;