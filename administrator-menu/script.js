const allSideMenu = document.querySelectorAll("#sidebar .side-menu.top li a");

allSideMenu.forEach((item) => {
  const li = item.parentElement;

  item.addEventListener("click", function () {
    allSideMenu.forEach((i) => {
      i.parentElement.classList.remove("active");
    });
    li.classList.add("active");
  });
});

// TOGGLE SIDEBAR
const menuBar = document.querySelector("#content nav .bx.bx-menu");
const sidebar = document.getElementById("sidebar");

menuBar.addEventListener("click", function () {
  sidebar.classList.toggle("hide");
});

const searchButton = document.querySelector("#content nav form .form-input button");
const searchButtonIcon = document.querySelector("#content nav form .form-input button .bx");
const searchForm = document.querySelector("#content nav form");

searchButton.addEventListener("click", function (e) {
  if (window.innerWidth < 576) {
    e.preventDefault();
    searchForm.classList.toggle("show");
    if (searchForm.classList.contains("show")) {
      searchButtonIcon.classList.replace("bx-search", "bx-x");
    } else {
      searchButtonIcon.classList.replace("bx-x", "bx-search");
    }
  }
});

if (window.innerWidth < 768) {
  sidebar.classList.add("hide");
} else if (window.innerWidth > 576) {
  searchButtonIcon.classList.replace("bx-x", "bx-search");
  searchForm.classList.remove("show");
}

window.addEventListener("resize", function () {
  if (this.innerWidth > 576) {
    searchButtonIcon.classList.replace("bx-x", "bx-search");
    searchForm.classList.remove("show");
  }
});

const switchMode = document.getElementById("switch-mode");

switchMode.addEventListener("change", function () {
  if (this.checked) {
    document.body.classList.add("dark");
  } else {
    document.body.classList.remove("dark");
  }
});

// Loading
function showLoadingAndRedirect(event, url) {
  // Jika event tersedia, cegah tindakan default link
  if (event) {
    event.preventDefault();
  }

  // Tampilkan overlay loading
  document.querySelector(".loading-overlay").style.display = "flex";

  // Setelah 2 detik, arahkan ke URL
  setTimeout(function () {
    window.location.href = url;
  }, 2000);
}

function showLoadingAndRedirect(url) {
  // Tampilkan overlay loading
  document.querySelector(".loading-overlay").style.display = "flex";

  // Setelah 2 detik, arahkan ke URL
  setTimeout(function () {
    window.location.href = url;
  }, 2000);
}

// Profile Pictures
function previewProfilePicture(event) {
  const preview = document.getElementById("profilePicturePreview");
  const file = event.target.files[0];
  const reader = new FileReader();

  reader.onload = function () {
    preview.src = reader.result;
  };

  if (file) {
    reader.readAsDataURL(file);
  }
}

// Text Area
// Fungsi untuk memperbarui hitung karakter
function updateCharacterCount() {
  var textarea = document.getElementById("customTextarea");
  var charCount = document.getElementById("charCount");
  var maxLength = textarea.getAttribute("maxlength");
  var currentLength = textarea.value.length;

  // Update teks di elemen charCount
  charCount.textContent = currentLength + "/" + maxLength;
}

// Memastikan hitung karakter diperbarui saat halaman dimuat
document.addEventListener("DOMContentLoaded", function () {
  updateCharacterCount();
});

// Memperbarui hitung karakter saat teks diubah
document.getElementById("customTextarea").addEventListener("input", updateCharacterCount);

// Update Settings
function previewProfilePicture(event) {
  var reader = new FileReader();
  reader.onload = function () {
    var output = document.getElementById("profilePicturePreview");
    output.src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
}

function updateCharacterCount() {
  var textarea = document.getElementById("customTextarea");
  var charCount = document.getElementById("charCount");
  charCount.textContent = textarea.value.length + "/254";
}

// Call the updateCharacterCount function on page load to update the initial character count
window.onload = function () {
  updateCharacterCount();
};
