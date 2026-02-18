import "./bootstrap";
import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

// Import clean form URL module
import "./modules/clean-form-url";

// Import UI component modules
import "./modules/helpers";
import "./modules/notification";
import "./modules/select";
import "./modules/table";
import "./modules/datepicker";
import "./modules/dropmenu";
import "./modules/chart";

// Import Filepond and plugins
import "./modules/filepond.min";
import "./modules/filepond-plugin-file-encode";
import "./modules/filepond-plugin-file-validate-size";
import "./modules/filepond-plugin-file-validate-type";
import "./modules/filepond-plugin-image-crop";
import "./modules/filepond-plugin-image-edit";
import "./modules/filepond-plugin-image-exif-orientation";
import "./modules/filepond-plugin-image-preview";
import "./modules/filepond-plugin-image-resize";
import "./modules/filepond-plugin-image-transform";
import "./modules/filepond-plugin-image-validate-size";

// Import additional UI modules
import "./modules/cropper.min";
import "./modules/animations";
