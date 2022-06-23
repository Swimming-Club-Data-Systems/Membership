// import { Formik, Form as FormikForm } from "formik";
import React, { useEffect, useState } from "react";
import * as yup from "yup";
// import { Button } from "react-bootstrap";
import Form from "../../components/form/Form";
import * as tenantFunctions from "../../classes/Tenant";
import DateInput from "../../components/form/DateInput";
import TextInput from "../../components/form/TextInput";
import Header from "../../components/Header";
import Breadcrumb from "../../components/Breadcrumb";
import { Alert, Card, ListGroup } from "react-bootstrap";
import axios from "axios";
import moment from "moment";

const renderMembers = (members) => {
  return (
    <>
      {
        members &&
        <ul className="list-unstyled">
          {
            members.map((member, idx) => (
              <li key={idx}>
                <h4>
                  {`${member.first_name} ${member.last_name}`}
                </h4>

                <dl className="row">
                  <dt className="col-6">
                    Date of birth
                  </dt>
                  <dd className="col-6">
                    {moment(member.date_of_birth).format("DD/MM/YYYY")}
                  </dd>

                  <dt className="col-6">
                    Age today
                  </dt>
                  <dd className="col-6">
                    {member.age_today}
                  </dd>

                  <dt className="col-6">
                    Age on day
                  </dt>
                  <dd className="col-6">
                    {member.age_on_day}
                  </dd>

                  <dt className="col-6">
                    ASA Number
                  </dt>
                  <dd className="col-6">
                    {member.ngb_id}
                  </dd>

                  <dt className="col-6">
                    ASA Type
                  </dt>
                  <dd className="col-6">
                    {member.ngb_category_name}
                  </dd>

                  {
                    member.gender_identity &&
                    <>
                      <dt className="col-6">
                        Gender Identity
                      </dt>
                      <dd className="col-6">
                        {member.gender_identity}
                      </dd>
                    </>
                  }
                </dl>
              </li>
            ))
          }
        </ul>
      }

      {
        !members &&
        <Alert variant="warning">
          No matches
        </Alert>
      }
    </>
  );
};

const JuniorLeagueMembers = () => {

  const [data, setData] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    tenantFunctions.setTitle("Junior and Arena League Members Report");
  }, []);

  const onClear = () => {
    setData(null);
  };

  const submit = async (values, { setSubmitting }) => {
    setSubmitting(true);

    try {

      const response = await axios.get("/api/admin/reports/league-members-report", {
        params: {
          min_age: values.minAge,
          max_age: values.maxAge,
          age_on_day: values.ageOn,
        }
      });

      if (response.data.success) {
        setSubmitting(false);
        setError(null);
        setData(response.data.members);
      } else {
        // There was an error
        setSubmitting(false);
        setError(response.data.message);
        setData(null);
      }

    } catch (error) {
      setSubmitting(false);
      setError("An unknown error has occurred");
      setData(null);
    }
  };

  const crumbs = [
    {
      to: "/admin",
      title: "Admin",
      name: "Admin",
    },
    {
      to: "/admin/reports",
      title: "Reports",
      name: "Reports",
    },
    {
      to: "/admin/reports/junior-league-report",
      title: "Eligible League Members Report",
      name: "League Members",
    },
  ];

  return (
    <>
      <Header breadcrumbs={<Breadcrumb crumbs={crumbs} />} title="Junior League Members" subtitle="Get a list of members valid for Junior or Arena League" />

      <div className="container-xl">
        <div className="row">
          <div className="col-lg-8">

            <Card className="mb-3">
              <Card.Body>

                {error &&
                  <Alert variant="danger">
                    {error}
                  </Alert>
                }

                <Form
                  initialValues={{
                    minAge: "",
                    maxAge: "",
                    ageOn: moment().format("YYYY-MM-DD"),
                  }}
                  validationSchema={yup.object({
                    minAge: yup.number("You must enter a minimum age").required("You must enter a minimum age").integer("You must enter a whole number").min(0, "You must enter a value greater than zero").max(120, "You must enter a value less than 120"),
                    maxAge: yup.number("You must enter a maximum age").required("You must enter a maximum age").integer("You must enter a whole number").min(0, "You must enter a value greater than zero").max(120, "You must enter a value less than 120").test({
                      name: "max-greater-than-min",
                      exclusive: false,
                      params: {},
                      message: "You must enter a maximum age which is greater than or equal to the minimum age",
                      test: function (value) {
                        return value >= parseInt(this.parent.minAge);
                      },
                    }).test({
                      name: "range-less-than-twenty",
                      exclusive: false,
                      params: {},
                      message: "The range between the minimum age and maximum age must not be greater than twenty",
                      test: function (value) {
                        return (value - parseInt(this.parent.minAge)) <= 20;
                      },
                    }),
                    ageOn: yup.date().required("You must enter a date").min("2000-01-01", "You must enter a date greater than 1 January 2000"),
                  })}
                  onSubmit={submit}
                  submitTitle="View report"
                  onClear={onClear}
                >
                  {/* <FormikForm> */}

                  <div className="row">
                    <div className="col-md">
                      <TextInput
                        label="Minimum age"
                        name="minAge"
                        type="number"
                        inputMode="numeric"
                        min="0"
                        max="120"
                        step="1"
                      />
                    </div>

                    <div className="col-md">
                      <TextInput
                        label="Maximum age"
                        name="maxAge"
                        type="number"
                        inputMode="numeric"
                        min="0"
                        max="120"
                        step="1"
                      />
                    </div>
                  </div>

                  <DateInput
                    label="Age on day"
                    name="ageOn"
                  />

                  {/* <Button type="submit">View report</Button> */}
                  {/* </FormikForm> */}
                </Form>
              </Card.Body>
            </Card>

            {data &&

              <>

                {
                  data.length == 0 &&
                  <Alert variant="warning">
                    <p className="mb-0">
                      <strong>No members in range</strong>
                    </p>
                    <p className="mb-0">
                      Please try a new selection
                    </p>
                  </Alert>
                }

                {
                  data.length > 0 &&
                  <Card>
                    <ListGroup variant="flush">
                      {
                        data.map((age) => (
                          <ListGroup.Item key={age.age}>
                            <h2>Age {age.age}</h2>

                            <div className="row">
                              <div className="col-md">
                                <h3>Male</h3>

                                {
                                  renderMembers(age.male)
                                }

                              </div>
                              <div className="col-md">
                                <h3>Female</h3>

                                {
                                  renderMembers(age.female)
                                }
                              </div>
                            </div>
                          </ListGroup.Item>
                        ))
                      }
                    </ListGroup>
                  </Card>
                }
              </>
            }

          </div>
        </div>
      </div>
    </>
  );

};

export default JuniorLeagueMembers;