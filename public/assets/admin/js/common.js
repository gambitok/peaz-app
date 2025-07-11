function readFileInput(input, functions) {
    var content = false;
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            if (window[functions]) {
                window[functions](e.target.result);
            } else {
                toastr.error("Invalid function Provided", 'error');
            }
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        toastr.error("Sorry - you're browser doesn't support the FileReader API", 'error');
    }
    return content;
}

function addOverlay() {
    $('#loader_display_d').show();
    //$(`<div id="overlayDocument"><img src="${loader_img}" /></div>`).appendTo(document.body);
}

function removeOverlay() {
    $('#loader_display_d').hide();
    //$('#overlayDocument').remove();
}

function Get_Unique_String(length) {
    length = (length === undefined) ? 10 : length;
    var result = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

function object_key_exits(obj, key = "") {
    return Object.keys(obj).indexOf(key) > -1;
}

function show_toastr_notification(msg = "", status = "200") {
    if (status == "200") {
        //toastr.success(msg);
        $.notify(msg,'success');
    } else if (status == "412") {
        $.notify(msg, "error");
       // toastr.error(msg);
    }
}

function ajax_maker(data) {
    // let ajax_demo = {
    //     url: "",
    //     type: "",
    //     data: "",
    //     success: "",
    //     error: "",
    // };
    let ajax_data = {
        url: (object_key_exits(data, 'url')) ? data.url : "",
        method: (object_key_exits(data, 'type')) ? data.type : "get",
        beforeSend: (object_key_exits(data, 'beforeSend')) ? data.beforeSend : addOverlay,
        complete: (object_key_exits(data, 'complete')) ? data.complete : removeOverlay,
        dataType: (object_key_exits(data, 'dataType')) ? data.dataType : 'JSON',
        success: (object_key_exits(data, 'success')) ? data.success : function () {
            alert('pass success');
        },
        error: function (err) {
            let json = err.responseJSON;
            if (json.status === 401) {
                window.location.assign("{{route('front.get_login')}}");
            } else if (json.status === 412) {
                show_toastr_notification(json.message, json.status);
            }

        }
    };
    if (object_key_exits(data, 'data')) {
        ajax_data.data = data.data;
    }
    if (object_key_exits(data, 'cache')) {
        ajax_data.cache = false;
    }
    if (object_key_exits(data, 'contentType')) {
        ajax_data.contentType = false;
    }
    if (object_key_exits(data, 'processData')) {
        ajax_data.processData = false;
    }
    if (object_key_exits(data, 'token')) {
        ajax_data.headers = {
            _token: "{{ csrf_token() }}"
        };
    }
    $.ajax(ajax_data);
}

function loadDate(){
    $(".date").datepicker({
        autoclose: true,
        todayHighlight: true,
        clearBtn: true,
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
       // dateFormat: 'dd-mm-yy'
    }).on('changeDate', function(ev) {
        $(this).valid();
     });

    $(".input-mask").inputmask();
}

function phoneNumberMethod(){
    jQuery.validator.addMethod("phone", function (phone_number, element) {
        phone_number = phone_number.replace(/\s+/g, "");
        return this.optional(element) || phone_number.length > 9 &&
              phone_number.match(/^\(?[\d\s]{3}-[\d\s]{3}-[\d\s]{4}$/);
    }, "Invalid phone number");
}

$(document).on('click','.general_edit_btn',function(){
    $(".input-mask").inputmask();
});

function preview_image(event){
    document.getElementById('blah').style.display='block';
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('blah');
        output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
 }

 function totalCalculate(val,pecentage,unit=0){
     if(unit == 0 || unit == 'undefined'){
         return val*pecentage;
     }else if(unit == "%"){
        return (val*pecentage) / 100;
     }else{
        return val*pecentage;
     }
 }

 function currencyFormate(values= 0){
    // Create our number formatter.
    var formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        // These options are needed to round to whole numbers if that's what you want.
        //minimumFractionDigits: 0, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
        //maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
    });

    return formatter.format(values); /* $2,500.00 */
 }

 function getTimezone(){
    return  Intl.DateTimeFormat().resolvedOptions().timeZone;
 }

document.addEventListener("DOMContentLoaded", function () {

    function updateFileButton(fileInput, button, fileType) {
        if (fileInput.files.length > 0) {
            button.textContent = 'Change';
        } else {
            button.textContent = 'Add new';
        }
    }

    document.querySelectorAll(".upload-file").forEach(input => {
        const button = document.getElementById(`${input.id}-btn`);

        input.addEventListener("change", function () {
            let file = this.files[0];
            let postId = this.dataset.id;
            let fileType = this.dataset.type;

            updateFileButton(input, button, fileType);

            if (!file) return;

            let formData = new FormData();
            formData.append("file", file);
            formData.append("type", fileType);
            formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);

            fetch(`/admin/post/${postId}/upload-file`, {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let previewContainer = document.getElementById(`${fileType}-preview-container`);
                        previewContainer.innerHTML = `
                            <div class="d-flex align-items-center mt-2">
                                <a href="${data.file_url}" target="_blank" id="${fileType}-link">
                                    <img src="${data.file_url}" alt="${fileType}" id="${fileType}-preview" style="max-width: 100px; max-height: 100px;">
                                </a>
                                <button class="btn btn-sm btn-danger ms-3 delete-file-btn" data-id="${postId}" data-type="${fileType}">Delete</button>
                            </div>
                        `;
                        updateFileButton(input, button, fileType);
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => console.error("Upload error:", error));
        });
    });

    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("delete-file-btn")) {
            let postId = event.target.dataset.id;
            let fileType = event.target.dataset.type;

            if (confirm(`Are you sure you want to delete this ${fileType}?`)) {
                fetch(`/admin/post/${postId}/delete-file?type=${fileType}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ type: fileType })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const previewContainer = document.getElementById(`${fileType}-preview-container`);
                            previewContainer.innerHTML = `
                                <p>No ${fileType} uploaded</p>
                            `;
                            const button = document.getElementById(`${fileType}-btn`);
                            button.textContent = 'Add new';
                        } else {
                            alert(`Error: ${data.message}`);
                        }
                    })
                    .catch(error => console.error("Error:", error));
            }
        }
    });

    // Оновлення кнопки вибору файлу
    document.querySelectorAll(".upload-file").forEach(input => {
        const button = document.getElementById(`${input.id}-btn`);
        button.addEventListener("click", function () {
            input.click();
        });
    });

});
