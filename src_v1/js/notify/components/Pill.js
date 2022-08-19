import React from "react";

class Pill extends React.Component {

  render() {
    // console.log(this.props);

    return (
      <div className="component-pill me-2 mb-1">
        <div className="component-pill-text-left">{this.props.data.name}</div>
        <button className="component-pill-button" type="button">
          <div className="component-pill-text-right" onClick={(event) => { this.props.handleClick(event, this.props.data); }}>
            <i className="fa fa-times" aria-hidden="true"></i>
          </div>
        </button>
      </div>
    );
  }
}

export default Pill;