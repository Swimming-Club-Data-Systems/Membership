import React from "react";

class Header extends React.Component {

  render() {

    return (
      <div className="bg-light mt-n3 py-3 mb-3">
        <div className="container-xl">

          {
            this.props.breadcrumbs
          }

          <h1>{this.props.title}</h1>

          {
            this.props.subtitle &&
            <p className="lead mb-0">{this.props.subtitle}</p>
          }
          
        </div>
      </div>
    );
  }
}

export default Header;