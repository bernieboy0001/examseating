// Confirm seating generation
function confirmGenerate() {
    return confirm("Are you sure you want to generate seating arrangement?");
}

// Validate capacity input
function validateCapacity() {
    let capacity = document.getElementById("capacity").value;
    if(capacity <= 0){
        alert("Capacity must be greater than zero");
        return false;
    }
    return true;
}

// Optional button hover effect
document.querySelectorAll("button").forEach(btn => {
    btn.addEventListener("mouseover", ()=> btn.style.background="#0056b3");
    btn.addEventListener("mouseout", ()=> btn.style.background="#007bff");
});