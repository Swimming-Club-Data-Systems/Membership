import React, { useContext, useRef, useState } from "react";
import { Button, ListGroup } from "react-bootstrap";
import * as Financials from "../../classes/Financials";
import { Collapse } from "bootstrap";
import { PaymentContext } from "./Checkout";

const ItemList = (props) => {

  // items={data.items} amount={data.amount} currency={data.currency}
  const data = useContext(PaymentContext);

  const subItemRefs = {};
  data.items.forEach(item => {
    subItemRefs[item.id] = useRef();
  });

  const handleSubItemShowHide = (ev) => {
    if (subItemRefs[ev?.target?.dataset?.target]) {
      const collapse = new Collapse(subItemRefs[ev?.target?.dataset?.target].current, { toggle: false });
      collapse.toggle();
    }
  };

  return (
    <>
      {
        data.items.length > 0 &&
        <ListGroup>
          {data.items.map((item) => {
            return (
              <ListGroup.Item key={item.id}>
                <div className="row">
                  <div className="col">
                    <h3 className="h5">{item.name}</h3>
                  </div>
                  <div className="col-auto ms-auto">
                    <p className="mb-0">
                      <strong>{Financials.formatCurrency(Financials.intToDec(item.amount), item.currency)}</strong>
                    </p>
                  </div>
                </div>

                <div className="row align-items-center">

                  {(item.subItems.length > 0 || item.description) &&
                    <>
                      {item.subItems &&
                        <div className="col">
                          <p className="mb-0">{item.subItems.length} sub-items</p>
                        </div>
                      }
                      <div className="col-auto ms-auto">
                        <p className="mb-0">
                          <Button onClick={handleSubItemShowHide} data-target={item.id} variant="secondary" size="sm">
                            Show details <i className="fa fa-caret-down" aria-hidden="true"></i>
                          </Button>
                        </p>
                      </div>
                    </>
                  }

                </div>

                <div className="collapse" ref={subItemRefs[item.id]} data-parent="#entry-list-group">
                  <div className="mt-3"></div>
                  {item.description &&
                    <p>{item.description}</p>
                  }

                  {item.subItems.length > 0 &&
                    <ul className="list-unstyled mb-0">
                      {item.subItems.map((subItem, idx) => {
                        return (
                          <li key={idx}>
                            <div className="row">
                              <div className="col-auto">
                                {subItem.name}
                              </div>
                              <div className="col-auto ms-auto">
                                {Financials.formatCurrency(Financials.intToDec(subItem.amount), subItem.currency)}
                              </div>
                            </div>
                          </li>
                        );
                      })}
                    </ul>
                  }
                </div>

              </ListGroup.Item>
            );
          })}
          {props.includeTotal &&
            <ListGroup.Item key="total">
              <div className="row align-items-center">
                <div className="col-6">
                  <p className="mb-0">
                    <strong>Total to pay</strong>
                  </p>
                </div>
                <div className="col text-end">
                  <p className="mb-0">
                    <strong>{Financials.formatCurrency(Financials.intToDec(data.amount), data.currency)}</strong>
                  </p>
                </div>
              </div>
            </ListGroup.Item>
          }
        </ListGroup>
      }
    </>
  );
};

export default ItemList;