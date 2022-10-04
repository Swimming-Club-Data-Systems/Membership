import React from "react";
import { Dropzone as DropzoneLib } from "dropzone";
import { post } from "jquery";

class Dropzone extends React.Component {
  constructor(props) {
    super(props);
    this.dropzoneRef = React.createRef();
    this.dropzone = null;
    this.state = {
      warningMessage: null,
    };
  }

  componentDidMount = () => {
    this.dropzone = new DropzoneLib(this.dropzoneRef.current, {
      url: this.props.action,
      method: "post",
      previewsContainer: "#upload-previews",
      // addRemoveLinks: true,
      filesizeBase: 1024,
      maxFilesize: this.props.maxFileSize,
      params: {
        notify_uuid: this.props.uuid,
        notify_date: this.props.date
      },
      previewTemplate: `\
<div class="col-6 col-md-4 col-lg-3 mb-2">
  <div class="border rounded p-2 text-center h-100">
    <div class="dz-preview dz-file-preview">
      <div class="dz-image"><i class="fa fa-file fa-3x" aria-hidden="true"></i></div>
      <div class="dz-details">
        <div class="align-items-center text-truncate"><span class="dz-success-mark"><i class="fa fa-check-circle text-success" aria-hidden="true"></i></span><span class="dz-error-mark"><i class="fa fa-times-circle text-danger" aria-hidden="true"></i></span> <span class="dz-filename"><span data-dz-name></span></span></div>
        <div class="align-items-center dz-size mb-2"><span data-dz-size></span> <button class="btn btn-sm btn-danger" alt="Remove file" data-dz-remove><i class="fa fa-trash" aria-hidden="true"></i></button></div>
      </div>
      <div class="progress">
        <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
      </div>
      <!--<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->
      <div class="dz-error-message"><span data-dz-errormessage></span></div>
    </div>
  </div>
</div>\
`,
    });

    this.dropzone.on("dragenter", (event) => {
      this.dropzoneRef.current.classList.add("bg-light");
    });

    this.dropzone.on("dragend", (event) => {
      this.dropzoneRef.current.classList.remove("bg-light");
    });

    this.dropzone.on("dragleave", (event) => {
      this.dropzoneRef.current.classList.remove("bg-light");
    });

    this.dropzone.on("dragover", (event) => {
      this.dropzoneRef.current.classList.add("bg-light");
    });

    this.dropzone.on("drop", (event) => {
      this.dropzoneRef.current.classList.remove("bg-light");
    });

    this.dropzone.on("success", (file, response) => {
      // console.log(file);
      if (response.status == 200) {
        let attachments = [
          ...this.props.attachments,
          {
            filename: response.name,
            filesize: response.size,
            mimetype: response.type,
            s3_key: response.key,
            url: response.url,
          }];
        this.props.setAttachments(attachments);
      } else {
        console.error(response);
      }
      this.printFileDetails();
    });

    this.dropzone.on("removedfile", (file) => {
      // console.log(file);

      let attachments = [...this.props.attachments];
      for (let i = 0; i < attachments.length; i++) {
        if (attachments[i].filename == file.name && attachments[i].mimetype == file.type) {
          attachments.splice(i, 1);
        }
      }
      this.props.setAttachments(attachments);
      this.printFileDetails();
    });
  };

  /**
       * Print file details on change
       * @param {Event} event 
       */
  printFileDetails = (event) => {
    // Set a default max file size
    var maxFileSize = 1024 * 1024; // 1 megabyte
    if (this.props.maxFileSize) {
      var maxFileSize = parseInt(this.props.maxFileSize);
    }

    // Set a default max total file size
    var maxTotalFileSize = 1024 * 1024; // 1 megabyte
    if (this.props.maxTotalFileSize) {
      var maxTotalFileSize = parseInt(this.props.maxTotalFileSize);
    }

    // Total file size
    var totalFileSize = 0;

    // Array of files too large
    var tooLarge = [];

    for (let i = 0; i < this.props.attachments.length; i++) {
      totalFileSize += this.props.attachments[i].filesize;
      var maxFileSizeOK = this.props.attachments[i].filesize <= maxFileSize;
      var maxTotalFileSizeOK = totalFileSize <= maxTotalFileSize;

      if (!maxFileSizeOK) {
        tooLarge.push({
          "name": attachments[i].filename,
          "size": attachments[i].size,
        });
      }
    }

    var maxTotalFileSizeOK = totalFileSize <= maxTotalFileSize;

    if (maxTotalFileSizeOK && tooLarge.length == 0) {
      this.setState({ warningMessage: null });
      this.props.setCanSubmitAttachments(true);
    } else {
      this.props.setCanSubmitAttachments(false);
      let warning = "Files too large";
      var errorMessage = "Files are too large or non-compliant";

      // Custom error messages
      if (tooLarge.length > 0) {
        var needsComma = false;
        errorMessage = "The following files are too large:";
        tooLarge.forEach(file => {
          if (needsComma) {
            errorMessage += ", ";
          }
          needsComma = true;
          errorMessage += file.name;
        });
        errorMessage += ".";

        if (!maxTotalFileSizeOK) {
          errorMessage += " The files also collectively exceed the maximum size for attachments.";
        }

      } else {
        errorMessage = "The files you are trying to upload are collectively bigger than the maximum size allowed. Please remove some files.";
      }

      warning = errorMessage;

      this.setState({ warningMessage: warning });
    }
  };

  render() {
    return (
      <>
        <label className="form-label">Add attachments</label>
        <div className="upload-drop card card-body mb-3" ref={this.dropzoneRef}>
          <div className="dz-message d-flex flex-column text-center py-2">
            <i className="fa fa-cloud-upload fa-3x" aria-hidden="true"></i>
            Drag &amp; Drop attachments here or click to browse for files
          </div>
          <div className="dropzone-previews row g-2 mb-n2" id="upload-previews"></div>
        </div>
        {
          this.state.warningMessage &&
          <p className="text-danger mt-n2">{this.state.warningMessage}</p>
        }
      </>
    );
  }
}

export default Dropzone;