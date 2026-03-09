// at top for debugging
// console.log("select-datetime.js loaded");

// document.addEventListener("click", function (e) {
//     // safer detection for elements inside button
//     let btn = e.target.closest(".time-btn");
//     if (!btn) return;

//     console.log("Time button clicked:", btn.dataset.time);

//     let selectedTime = btn.dataset.time;
//     let selectedDate = document.getElementById("appointment_date").value;

//     // sanity checks
//     if (!selectedDate) {
//         console.log("No date selected");
//         return;
//     }
//     if (!selectedTime) {
//         console.log("No time in data-time attribute");
//         return;
//     }

//     let xhr = new XMLHttpRequest();
//     xhr.open("POST", "store-datetime.php", true);

//     xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

//     xhr.onload = function () {
//         console.log("store-datetime response:", this.responseText);
//         if (this.responseText.trim() === "success") {
//             window.location.href = "confirmation.php";
//         } else {
//             // helpful debug message
//             alert("Could not store appointment. Response: " + this.responseText);
//         }
//     }

//     xhr.onerror = function () {
//         console.log("AJAX request failed");
//     }

//     xhr.send("date=" + encodeURIComponent(selectedDate) + "&time=" + encodeURIComponent(selectedTime));
// });





//above code is helpful for debugging
document.addEventListener("click", function (e) {

    if (e.target.classList.contains("time-btn")) {

        let selectedTime = e.target.dataset.time;
        let selectedDate =
            document.getElementById("appointment_date").value;

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "store-datetime.php", true);
        xhr.setRequestHeader("Content-type",
            "application/x-www-form-urlencoded");

        // xhr.onload = function () {
        //     if (this.responseText == "success") {
        //         window.location.href = "confirmation.php";
        //     }
        // }

        xhr.send("date=" + selectedDate + "&time=" + selectedTime);
    }
});


// 🔥 Function to load slots
function loadTimeSlots(date) {

    let staffId = document.querySelector("input[name='staff_id']").value;
    let duration = document.querySelector("input[name='total_duration']").value;

    fetch("../user/fetch-slots.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "date=" + date +
            "&staff_id=" + staffId +
            "&duration=" + duration
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById("time_slots").innerHTML = data;
        })
        .catch(error => {
            console.log("Error loading slots:", error);
        });
}


// 🔥 this function shows time in appointment summary
document.addEventListener("click", function (e) {

    if (e.target.classList.contains("time-btn")) {
        e.preventDefault();   // 🔥 ADD THIS LINE

        // Remove previous selection
        document.querySelectorAll(".time-btn")
            .forEach(btn => btn.classList.remove("selected"));

        // Highlight clicked slot
        e.target.classList.add("selected");

        // Get selected time
        let selectedTime = e.target.getAttribute("data-time");

        // Store in hidden input
        document.getElementById("selected_time").value = selectedTime;

        // Show in summary
        document.getElementById("summary_time").innerText = selectedTime;

        // Enable confirm button
        document.querySelector(".confirm-btn").disabled = false;
    }
});


//new
document.addEventListener("click", function(e){

if(e.target.classList.contains("pay-btn")){

    // remove previous selection
    document.querySelectorAll(".pay-btn")
    .forEach(btn => btn.classList.remove("selected"));
    // console.log("Payment button clicked");

    e.target.classList.add("selected");

    let amount = e.target.dataset.amount;
    // console.log(amount);

    // store hidden input
    document.getElementById("selected_payment").value = amount;

    // show in summary
    document.getElementById("summary_payment").innerText = amount;

    // AJAX store in session
    let xhr = new XMLHttpRequest();
    xhr.open("POST","store-datetime.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.send("amount="+amount);

    // checkNextButton();
}

});

