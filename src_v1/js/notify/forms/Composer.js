import React from "react";
import Header from "../../components/Header.js";
import ToRow from "../components/ToRow";
import DropdownRow from "../components/DropdownRow";
import TextRow from "../components/TextRow";
import Modal from "react-bootstrap/Modal";
import Button from "react-bootstrap/Button";
import Tabs from "react-bootstrap/Tabs";
import Tab from "react-bootstrap/Tab";
import Form from "react-bootstrap/Form";
import { Editor } from "@tinymce/tinymce-react";
import axios from "axios";
import Dropzone from "../components/Dropzone";
import Accordion from "react-bootstrap/Accordion";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import { Navigate } from "react-router-dom";
import Dropdown from "../components/Dropdown";
import * as tenantFunctions from "../../classes/Tenant";
import Loader from "../../components/Loader.js";
import Breadcrumb from "../../components/Breadcrumb.js";
// import exports from "enhanced-resolve";

export class Composer extends React.Component {

  constructor() {
    super();
    document.title = "Notify Composer";
    this.editor = React.createRef();
    this.state = {
      loaded: false,

      from: "asClub",
      replyTo: "toClub",
      subject: "",
      attachments: [],
      message: "",
      forceSend: false,
      canForceSend: false,
      sendToCoaches: true,
      category: "DEFAULT",

      // Modals
      showTo: false,
      showDemoSubmission: false,
      showUserSettingsDialog: false,
      showConfirmSendDialog: false,

      // Options for this
      possibleTos: [],
      possibleFroms: [],
      possibleReplyTos: [],
      possibleCategories: [],

      // TinyMCE
      documentBaseUrl: null,
      imagesUploadUrl: null,
      tinyMceCssLocation: null,
      editorValue: "",

      // Tabs
      tabKey: "editor",

      // Dropzone
      emailUuid: null,
      date: null,
      dzMaxFileSize: 10485760,
      dzMaxTotalFileSize: 10485760,
      canSubmitAttachments: true,

      validated: false,
      emailSent: false,

      disableSendConfirm: false,
      disableSettingsSave: false,

      settings: {
        replyEmailAddress: "",
        defaultReplyTo: "toClub",
        defaultSendAs: "asClub"
      }
    };
  }

  componentDidMount() {
    axios.get("/notify/new/react-data")
      .then(response => {
        let data = response.data;
        this.setState({
          possibleTos: data.possibleTos,
          possibleReplyTos: data.possibleReplyTos,
          possibleFroms: data.possibleFroms,
          canForceSend: data.canForceSend,
          possibleCategories: data.possibleCategories,
          documentBaseUrl: data.documentBaseUrl,
          imagesUploadUrl: data.imagesUploadUrl,
          tinyMceCssLocation: data.tinyMceCssLocation,
          date: data.date,
          emailUuid: data.uuid,
          loaded: true,
          from: data.settings.defaultSendAs,
          replyTo: data.settings.defaultReplyTo,
          settings: data.settings,
        });
      })
      .catch(function (error) {
        console.log(error);
      });
  }

  handlePillClick = (event, data) => {
    this.handleTosChangeBackend(data.type, data.key, false);
  };

  handleChange = (event) => {
    // Update state
    const { name, value, type, checked } = event.target;
    if (type === "checkbox") {
      this.setState({
        [name]: checked,
      });
    } else {
      this.setState({
        [name]: value,
      });
    }
  };

  handleSettingsChange = (event) => {
    // Update state
    const { name, value, type, checked } = event.target;
    if (type === "checkbox") {
      this.setState(prevState => ({
        ...prevState,
        settings: {
          ...prevState.settings,
          [name]: checked,
        },
      })
      );
    } else {
      this.setState(prevState => ({
        ...prevState,
        settings: {
          ...prevState.settings,
          [name]: value,
        },
      })
      );
    }
  };

  handleShowTo = () => {
    this.setState({
      showTo: true,
    });
  };

  handleCloseTo = () => {
    this.setState({
      showTo: false,
    });
  };

  handleSubmit = (event) => {
    event.preventDefault();
    // console.log(event);

    const form = event.currentTarget;
    if (form.checkValidity() === false || this.getGroupList().length === 0 || !this.state.canSubmitAttachments || this.state.editorValue.length == 0) {
      event.stopPropagation();
    } else {
      this.setState({
        showConfirmSendDialog: true,
      });
    }

    this.setState({
      validated: true,
    });
  };

  handleConfirmSend = () => {
    this.setState({
      disableSendConfirm: true,
    });

    const list = this.getGroupList();
    let recipients = {};

    list.forEach(element => {
      if (element.state) {
        recipients[element.key] = element;
      }
    });

    axios.post("/notify/send-email", {
      state: this.state,
      recipients,
    })
      .then(response => {
        let data = response.data;
        console.info(data);
        this.setState({
          showConfirmSendDialog: false,
          emailSent: true,
        });
      })
      .catch(error => {
        console.warn(error);
        this.setState({
          disableSendConfirm: false,
        });
      });
  };

  handleConfirmSendClose = () => {
    this.setState({
      showConfirmSendDialog: false,
    });
  };

  setAttachments = (attachments) => {
    this.setState({
      attachments
    });
  };

  setCanSubmitAttachments = (state) => {
    this.setState({
      canSubmitAttachments: state,
    });
  };

  handleTosChange = (event, type) => {
    const { name, checked } = event.target;

    this.handleTosChangeBackend(type, name, checked);

    // const groups = [...this.state.possibleTos[type].groups];
    // this.setState(prevState => ({
    //   ...prevState,
    //   possibleTos: {
    //     ...prevState.possibleTos,
    //     [type]: {
    //       ...prevState.possibleTos[type],
    //       groups: groups.map((data, idx) => {
    //         if (name === data.key) {
    //           return {
    //             ...data,
    //             state: checked,
    //           }
    //         } else {
    //           return { ...data };
    //         }
    //       }),
    //     },
    //   },
    // })
    // )
  };

  handleTosChangeBackend = (type, name, checked) => {
    const groups = [...this.state.possibleTos[type].groups];
    this.setState(prevState => ({
      ...prevState,
      possibleTos: {
        ...prevState.possibleTos,
        [type]: {
          ...prevState.possibleTos[type],
          groups: groups.map((data) => {
            if (name === data.key) {
              return {
                ...data,
                state: checked,
              };
            } else {
              return { ...data };
            }
          }),
        },
      },
    })
    );
  };

  handleSquadTosChange = (event) => {
    this.handleTosChange(event, "squads");
  };

  handleListTosChange = (event) => {
    this.handleTosChange(event, "lists");
  };

  handleGalaTosChange = (event) => {
    this.handleTosChange(event, "galas");
  };

  handleDemoOpen = () => {
    this.setState({
      showDemoSubmission: true
    });
  };

  handleDemoClose = () => {
    this.setState({
      showDemoSubmission: false
    });
  };

  handleForceSendChange = (event) => {
    if (event.target.checked) {
      this.setState({
        showForceSendWarning: true
      });
    } else {
      this.setState({
        forceSend: false
      });
    }
  };

  handleForceSendClose = () => {
    this.setState({
      showForceSendWarning: false
    });
  };

  handleForceSendConfirm = () => {
    this.setState({
      showForceSendWarning: false,
      forceSend: true,
    });
  };

  handleSettingsOpen = () => {
    this.setState({
      showUserSettingsDialog: true
    });
  };

  handleSettingsClose = () => {
    this.setState({
      showUserSettingsDialog: false
    });
  };

  handleSettingsSave = () => {
    this.setState({
      disableSettingsSave: true,
    });

    axios.post("/notify/save-user-settings", {
      ...this.state.settings
    })
      .then(response => {
        let data = response.data;
        this.setState({
          settings: data.settings,
          possibleReplyTos: data.possibleReplyTos,
          showUserSettingsDialog: false,
          disableSettingsSave: false,
        });
      })
      .catch(function (error) {
        console.log(error);
        this.setState({
          disableSettingsSave: false,
        });
      });
  };

  getGroupList = () => {
    return [...this.state.possibleTos.squads.groups, ...this.state.possibleTos.lists.groups, ...this.state.possibleTos.galas.groups];
  };

  getToListCount = () => {
    let count = 0;
    let list = this.getGroupList();
    for (let index = 0; index < list.length; index++) {
      if (list[index].state) {
        count += 1;
      }
    }
    return count;
  };

  renderPreview = () => {
    tenantFunctions.setTitle("Notify Composer");

    const attachments = this.state.attachments.map((data) => {
      return (
        <li key={data.s3_key}><a href={"/files/" + data.url} target="_blank" rel="noreferrer">{data.filename}</a></li>
      );
    });

    return (
      <>
        <p>This is a preview of your email.</p>

        {
          this.state.attachments.length > 0 &&
          <div className="card mb-3">
            <div className="card-header">
              Attachments
            </div>
            <div className="card-body">
              <ul className="list-unstyled mb-0">
                {attachments}
              </ul>
            </div>
          </div>
        }

        <div className="card">
          <div className="card-header">
            Content
          </div>
          <div className="card-body">
            <p>Hello -USER-NAME-,</p>
            <div dangerouslySetInnerHTML={{ __html: this.state.editorValue }} />
          </div>
        </div>
      </>
    );
  };

  render() {

    let squads, targetedLists, galaEntrants;
    if (this.state.loaded) {
      squads = this.state.possibleTos.squads.groups.map(data => {
        return (
          <Col xs={6} md={4} lg={3} key={data.key}>
            <Form.Check
              type="checkbox"
              id={data.key}
              label={data.name}
              checked={data.state}
              onChange={this.handleSquadTosChange}
              name={data.key}
            />
          </Col>
        );
      });

      targetedLists = this.state.possibleTos.lists.groups.map(data => {
        return (
          <Col xs={6} md={4} lg={3} key={data.key}>
            <Form.Check
              type="checkbox"
              id={data.key}
              label={data.name}
              checked={data.state}
              onChange={this.handleListTosChange}
              name={data.key}
            />
          </Col>
        );
      });

      galaEntrants = this.state.possibleTos.galas.groups.map(data => {
        return (
          <Col xs={6} md={4} lg={3} key={data.key}>
            <Form.Check
              type="checkbox"
              id={data.key}
              label={data.name}
              checked={data.state}
              onChange={this.handleGalaTosChange}
              name={data.key}
            />
          </Col>
        );
      });
    }

    const crumbs = [
      {
        to: "/notify",
        title: "Notify",
        name: "Notify",
      },
      {
        to: "/notify/new",
        title: "Composer",
        name: "Composer",
      },
    ];

    return (

      <div>

        {
          this.state.emailSent &&
          <Navigate to="/notify/new/success" />
        }

        <Header breadcrumbs={<Breadcrumb crumbs={crumbs} />} title="Send a new email" subtitle="Send emails to targeted groups"/>

        <Loader loaded={this.state.loaded}>

          {this.state.loaded &&
            <>

              <Form className="container-xl" onSubmit={this.handleSubmit} validated={this.state.validated} noValidate>
                <ToRow groups={this.getGroupList()} numTos={this.getToListCount()} validated={this.state.validated} handleClick={this.handlePillClick} handleShowTo={this.handleShowTo} />
                <DropdownRow name="from" options={this.state.possibleFroms} label="Send as" formValue={this.state.from} handleChange={this.handleChange} />
                <DropdownRow name="replyTo" options={this.state.possibleReplyTos} label="Send replies to" formValue={this.state.replyTo} handleChange={this.handleChange} />
                <DropdownRow name="category" options={this.state.possibleCategories} label="Subscription category" formValue={this.state.category} handleChange={this.handleChange} />
                <TextRow name="subject" label="Subject" formValue={this.state.subject} handleChange={this.handleChange} required />
                <Tabs id="tabs" activeKey={this.state.tabKey} onSelect={(k) => this.setState({ tabKey: k })} className="mb-3">
                  <Tab eventKey="editor" title="Editor">
                    <div className="mb-3">
                      <Editor
                        tinymceScriptSrc="/js/tinymce/5/tinymce.min.js"
                        onInit={(evt, editor) => this.editor.current = editor}
                        onEditorChange={(value) => { this.setState({ editorValue: value }); }}
                        // initialValue={this.state.editorValue}
                        init={{
                          skin: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "oxide-dark" : ""),
                          relative_urls: false,
                          remove_script_host: false,
                          document_base_url: document.documentURI,
                          selector: "#message",
                          images_upload_url: "/notify/new/image-upload",
                          automatic_uploads: true,
                          images_upload_credentials: true,
                          branding: false,
                          plugins: [
                            "autolink lists link image charmap print preview anchor",
                            "searchreplace visualblocks code autoresize insertdatetime media table",
                            "paste help wordcount"
                          ],
                          statusbar: false,
                          paste_as_text: true,
                          toolbar: "insert | undo redo |  formatselect | bold italic underline | bullist numlist outdent indent | removeformat | help",
                          content_css: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "default"),
                          fontsize_formats: "12pt",
                          font_formats: "Default=system-ui,-apple-system,\"Segoe UI\",Roboto,\"Helvetica Neue\",Arial,\"Noto Sans\",\"Liberation Sans\",sans-serif,\"Apple Color Emoji\",\"Segoe UI Emoji\",\"Segoe UI Symbol\",\"Noto Color Emoji\";",
                          style_formats: [
                            {
                              title: "Headings", items: [
                                { title: "Heading 1", format: "h1" },
                                { title: "Heading 2", format: "h2" },
                                { title: "Heading 3", format: "h3" },
                                { title: "Heading 4", format: "h4" },
                                { title: "Heading 5", format: "h5" },
                                { title: "Heading 6", format: "h6" }
                              ]
                            },
                            {
                              title: "Inline", items: [
                                { title: "Bold", format: "bold" },
                                { title: "Italic", format: "italic" },
                                { title: "Underline", format: "underline" },
                                { title: "Strikethrough", format: "strikethrough" },
                                { title: "Superscript", format: "superscript" },
                                { title: "Subscript", format: "subscript" },
                                { title: "Code", format: "code" }
                              ]
                            },
                            {
                              title: "Blocks", items: [
                                { title: "Paragraph", format: "p" },
                                { title: "Blockquote", format: "blockquote" },
                                { title: "Div", format: "div" },
                                { title: "Pre", format: "pre" }
                              ]
                            }
                          ],
                          menu: {
                            edit: { title: "Edit", items: "undo redo | cut copy paste | selectall | searchreplace" },
                            view: { title: "View", items: "code | visualaid visualchars visualblocks | spellchecker | preview fullscreen" },
                            insert: { title: "Insert", items: "image link template codesample inserttable | charmap emoticons hr | pagebreak nonbreaking anchor toc | insertdatetime" },
                            format: { title: "Format", items: "bold italic underline strikethrough superscript subscript codeformat | formats blockformats | removeformat" },
                            tools: { title: "Tools", items: "spellchecker spellcheckerlanguage | code wordcount" },
                            table: { title: "Table", items: "inserttable | cell row column | tableprops deletetable" },
                            help: { title: "Help", items: "help" }
                          },

                          //toolbar: "link",
                        }}
                      />
                    </div>

                    <p>
                      <Button type="submit" variant="success" onSubmit={this.handleSubmit}>Send the email</Button> <Button variant="dark" onClick={() => { this.setState({ tabKey: "preview" }); }}>Preview message</Button>
                    </p>
                  </Tab>
                  <Tab eventKey="attachments" title="Attachments">
                    <Dropzone action="/notify/file-uploads" uuid={this.state.emailUuid} date={this.state.date} maxTotalFileSize={this.state.dzMaxTotalFileSize} maxFileSize={this.state.dzMaxFileSize} setAttachments={this.setAttachments} attachments={this.state.attachments} canSubmitAttachments={this.state.canSubmitAttachments} setCanSubmitAttachments={this.setCanSubmitAttachments} />
                  </Tab>
                  <Tab eventKey="preview" title="Preview">
                    <div className="mb-3">
                      {this.renderPreview()}
                    </div>
                  </Tab>
                  <Tab eventKey="advanced" title="Advanced">
                    <div className="mb-3">
                      {this.state.canForceSend &&
                        <Form.Check
                          type="checkbox"
                          id="force-send"
                          label="Force send this email"
                          checked={this.state.forceSend}
                          onChange={this.handleForceSendChange}
                          name="forceSend"
                        />
                      }

                      <Form.Check
                        type="checkbox"
                        id="send-to-coaches"
                        label="Send to coaches of included squads"
                        checked={this.state.sendToCoaches}
                        onChange={this.handleChange}
                        name="sendToCoaches"
                      />
                    </div>

                    <p>
                      <Button type="button" variant="primary" onClick={this.handleSettingsOpen}>
                        <i className="fa fa-cog" aria-hidden="true"></i> Change Settings
                      </Button>{" "}
                      <Button type="button" variant="info" onClick={this.handleDemoOpen}>
                        Developer Info
                      </Button>
                    </p>
                  </Tab>
                </Tabs>
              </Form>

              <Modal show={this.state.showTo} onHide={this.handleCloseTo} centered fullscreen>
                <Modal.Header closeButton>
                  <Modal.Title>Add recipients</Modal.Title>
                </Modal.Header>
                <Modal.Body className="p-0">
                  <Accordion defaultActiveKey="0" flush>
                    {
                      squads.length > 0 &&
                      <Accordion.Item eventKey="0">
                        <Accordion.Header>Squads</Accordion.Header>
                        <Accordion.Body>
                          <Row>
                            {squads}
                          </Row>
                        </Accordion.Body>
                      </Accordion.Item>
                    }
                    {
                      targetedLists.length > 0 &&
                      <Accordion.Item eventKey="1">
                        <Accordion.Header>Targeted Lists</Accordion.Header>
                        <Accordion.Body>
                          <Row>
                            {targetedLists}
                          </Row>
                        </Accordion.Body>
                      </Accordion.Item>
                    }
                    {
                      galaEntrants.length > 0 &&
                      <Accordion.Item eventKey="2">
                        <Accordion.Header>Gala Entrants</Accordion.Header>
                        <Accordion.Body>
                          <Row>
                            {galaEntrants}
                          </Row>
                        </Accordion.Body>
                      </Accordion.Item>
                    }
                  </Accordion>
                </Modal.Body>
                <Modal.Footer>
                  <Button variant="primary" onClick={this.handleCloseTo}>
                    Save and close
                  </Button>
                </Modal.Footer>
              </Modal>

              <Modal show={this.state.showForceSendWarning} onHide={this.handleForceSendClose} centered>
                <Modal.Header closeButton className="bg-danger text-white">
                  <Modal.Title>Are you sure?</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                  <p>
                    <strong>Force sending an email overrides the subscription options of your members.</strong>
                  </p>

                  <p>
                    Under the General Data Protection Regulation, you may only override these preferences in specific cases.
                  </p>

                  <p className="mb-0">
                    SCDS may periodically review your organisation&apos;s use of the Force Send functionality.
                  </p>
                </Modal.Body>
                <Modal.Footer>
                  <Button variant="dark" onClick={this.handleForceSendClose}>
                    Don&apos;t force send
                  </Button>
                  <Button variant="danger" onClick={this.handleForceSendConfirm}>
                    I understand
                  </Button>
                </Modal.Footer>
              </Modal>

              <Modal show={this.state.showDemoSubmission} onHide={this.handleDemoClose} centered>
                <Modal.Header closeButton>
                  <Modal.Title>Submission Demo</Modal.Title>
                </Modal.Header>
                <Modal.Body>

                  <h2>Email preview</h2>

                  <div className="mb-3">
                    {this.renderPreview()}
                  </div>

                  <h2>For Developers: React Component State</h2>

                  <pre>{JSON.stringify(this.state, undefined, 2)}</pre>
                </Modal.Body>
                <Modal.Footer>
                  <Button variant="primary" onClick={this.handleDemoClose}>
                    Close
                  </Button>
                </Modal.Footer>
              </Modal>

              <Modal show={this.state.showConfirmSendDialog} onHide={this.handleConfirmSendClose} centered>
                <Modal.Header closeButton>
                  <Modal.Title>Confirm send?</Modal.Title>
                </Modal.Header>
                <Modal.Body>

                  <p className="mb-0">
                    Are you sure you want to send your email? Emails can&apos;t be unsent.
                  </p>

                </Modal.Body>
                <Modal.Footer>
                  <Button variant="dark" onClick={this.handleConfirmSendClose}>
                    Cancel
                  </Button>
                  <Button variant="primary" onClick={this.handleConfirmSend} disabled={this.state.disableSendConfirm}>
                    Confirm
                  </Button>
                </Modal.Footer>
              </Modal>

              <Modal show={this.state.showUserSettingsDialog} onHide={this.handleSettingsClose} centered>
                <Modal.Header closeButton>
                  <Modal.Title>Change your Notify Settings</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                  <Form onSubmit={this.handleSettingsSave}>
                    <Form.Group className="mb-3" controlId="replyEmailAddress">
                      <Form.Label>Reply-to email address</Form.Label>
                      <Form.Control type="email" placeholder="name@example.com" name="replyEmailAddress" value={this.state.settings.replyEmailAddress} onChange={this.handleSettingsChange} />
                    </Form.Group>
                    <Dropdown name="defaultSendAs" label="Default send email as" formValue={this.state.settings.defaultSendAs} handleChange={this.handleSettingsChange} options={this.state.possibleFroms} />
                    {
                      this.state.possibleReplyTos.length > 1 &&
                      <Dropdown name="defaultReplyTo" label="Default reply-to address" formValue={this.state.settings.defaultReplyTo} handleChange={this.handleSettingsChange} options={this.state.possibleReplyTos} />
                    }
                  </Form>
                </Modal.Body>
                <Modal.Footer>
                  <Button variant="dark" onClick={this.handleSettingsClose}>
                    Close
                  </Button>
                  <Button variant="primary" type="submit" onClick={this.handleSettingsSave} disabled={this.state.disableSettingsSave}>
                    Save
                  </Button>
                </Modal.Footer>
              </Modal>
            </>
          }
        </Loader>
      </div>
    );
  }
}

export default Composer;
