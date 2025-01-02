// JavaScript for handling modal
document.addEventListener("DOMContentLoaded", function () {
  var modal = document.getElementById("updateModal");
  var closeBtn = document.querySelector(".modal .close");

  // Function to open the modal
  document.querySelectorAll(".btn-icon-success").forEach(function (button) {
    button.onclick = function (event) {
      event.preventDefault();

      var id = this.getAttribute("data-id");
      var username = this.getAttribute("data-username");
      var email = this.getAttribute("data-email");
      var profilePicture = this.getAttribute("data-profile-picture");

      document.getElementById("updateId").value = id;
      document.getElementById("updateUsername").value = username;
      document.getElementById("updateEmail").value = email;

      // Set default profile picture if empty
      document.getElementById("updateProfilePicture").value = "";

      modal.style.display = "block";
    };
  });

  // Function to close the modal
  closeBtn.onclick = function () {
    modal.style.display = "none";
  };

  // Close the modal if user clicks outside of it
  window.onclick = function (event) {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  };
});
