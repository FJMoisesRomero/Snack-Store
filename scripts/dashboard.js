document.addEventListener("DOMContentLoaded", function() {
    const navToggle = document.getElementById("navToggle");
    const sidebar = document.getElementById("sidebar");
    const contentContainer = document.getElementById("contentContainer");

    navToggle.addEventListener("click", function() {
        if (sidebar.classList.contains("show")) {
            sidebar.classList.remove("show");
            sidebar.classList.add("hidden");
            contentContainer.classList.toggle("expanded");
        } else if (sidebar.classList.contains("hidden")) {
            sidebar.classList.remove("hidden");
            sidebar.classList.add("show");
            contentContainer.classList.remove("expanded");
        } else {
            sidebar.classList.add("show");
            contentContainer.classList.remove("expanded");
        }
    });
});
