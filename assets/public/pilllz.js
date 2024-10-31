/* global bootstrap */
document.addEventListener('DOMContentLoaded',function(){
    
    const pilllz_url = "https://pilllz.com";
    const pilllz_api_url = pilllz_url+"/api/";
    
    var pilllz_generatorDiv = document.getElementById("pilllz_generator");
    const pilllz_generatorBtns = document.getElementsByClassName("pilllz-edit-avatar");
    
    var pilllz_apikey = false;
    var pilllz_client_id = false;
    var pilllz_user_id = false;
    var pilllz_lang = 'fr';
    
    if(pilllz_generatorDiv === null){
        if(pilllz_generatorBtns === null){
            textToDiv('Pilllz Error : You need to add a div with ID pilllz_generator');
        }else{
            for(var i = 0; i < pilllz_generatorBtns.length; i++) {(
                function(index) {
                    pilllz_generatorBtns[index].addEventListener("click", function(ev) {
                        ev.preventDefault();
                        
                        pilllz_apikey = pilllz_generatorBtns[index].getAttribute('pilllz-apikey');
                        pilllz_client_id = pilllz_generatorBtns[index].getAttribute('pilllz-client_id');
                        pilllz_user_id = pilllz_generatorBtns[index].getAttribute('pilllz-user_id');
                        var pilllz_lang_tmp = pilllz_generatorBtns[index].getAttribute('pilllz-lang');
                        if(pilllz_lang_tmp === "en"){
                            pilllz_lang = "en";
                        }

                        var errorMessage = false;
                        if(pilllz_apikey === null){
                            errorMessage = 'Pilllz Error : You need to add a apikey param to your pilllz_generator div';
                        }else if(pilllz_client_id === null){
                            errorMessage = 'Pilllz Error : You need to add a clientid param to your pilllz_generator div';
                        }else if(pilllz_user_id === null){
                            errorMessage = 'Pilllz Error : You need to add a userid param to your pilllz_generator div';
                        }
                        if(errorMessage){
                            var errorDiv = document.createElement('div');
                            errorDiv.innerHTML = errorMessage;
                            pilllz_generatorBtns[index].parentNode.insertBefore(errorDiv, pilllz_generatorBtns[index].nextSibling);
                        }else{
                            bootstrapPilllz();
                            PilllzOpen(ev);
                        }
                    });
                })(i);
            }
        }
    }else{
        pilllz_apikey = pilllz_generatorDiv.getAttribute('pilllz-apikey');
        pilllz_client_id = pilllz_generatorDiv.getAttribute('pilllz-client_id');
        pilllz_user_id = pilllz_generatorDiv.getAttribute('pilllz-user_id');
        var pilllz_lang_tmp = pilllz_generatorDiv.getAttribute('pilllz-lang');
        if(pilllz_lang_tmp === "en"){
            pilllz_lang = "en";
        }
        if(pilllz_apikey === null){
            textToDiv('Pilllz Error : You need to add a apikey param to your pilllz_generator div');
        }else if(pilllz_client_id === null){
            textToDiv('Pilllz Error : You need to add a clientid param to your pilllz_generator div');
        }else if(pilllz_user_id === null){
            textToDiv('Pilllz Error : You need to add a userid param to your pilllz_generator div');
        }else{
            bootstrapPilllz();
        }
    }
    
    
    function bootstrapPilllz(){
        let xmlhttp = new XMLHttpRequest();
        let url = pilllz_api_url+"getpasskey/" + pilllz_client_id + "/" + pilllz_apikey + "/" + pilllz_user_id + '/' + pilllz_lang;

        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            let result = JSON.parse(this.responseText);
            if(result.result){
                if(result.passkey){                    
                    PilllzInteractions(result.passkey, pilllz_user_id);
                }else{
                    textToDiv("Unknown error : please contact dev@meepha.com if this appears again :o");
                }
            }else{
                textToDiv('Incorrect parameters : Are you sure of your key and client_id ?');
                if(result.msgs){
                    result.msgs.forEach((message) => {
                      textToDiv(message);
                    });
                }
            }
          }
        };
        xmlhttp.onerror = function() {
          console.error("Error with request");
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }
    
    function textToDiv(string, erase = false){
        if(pilllz_generatorDiv !== null){
            if(erase){
                pilllz_generatorDiv.innerHTML = '';
            }
            let currentHtml = pilllz_generatorDiv.innerHTML;
            pilllz_generatorDiv.innerHTML = currentHtml+string;
        }else{
            hideModal();
            alert(string);
        }
    }

    // PilllzInteractions

    var Iframe = document.createElement('iframe');
    Iframe.classList.add('pilllz-generator-iframe');
    Iframe.title = 'Pillz generator';
    
    var imageTag = document.createElement('img');
    imageTag.classList.add('pilllz-avatar');

    var PilllzModal = document.createElement('div');
    var modalContent;
    var PilllzBsModal;

    function PilllzInteractions(passkey, user_id){
        
        // loadimage
        imageTag.src = pilllz_url+`/avatar/`+user_id+`/svg`;
        if(pilllz_generatorDiv !== null){
            pilllz_generatorDiv.innerHTML = '';
            pilllz_generatorDiv.appendChild(imageTag);
            pilllz_generatorDiv.addEventListener('click', PilllzOpen);
        }

        // construct iframe
        var iframeUrl = pilllz_url+"/widget/editor/"+user_id+"/"+passkey;
        Iframe.src = iframeUrl;
        

        // listen to elements with class .pilllz-edit-avatar
        var PillzEditAvatarElems = document.getElementsByClassName('pilllz-edit-avatar');
        if(PillzEditAvatarElems !== null && PillzEditAvatarElems !== undefined && PillzEditAvatarElems.length > 0){
            for (var i = 0; i < PillzEditAvatarElems.length; i++) {
                PillzEditAvatarElems[i].addEventListener('click', PilllzOpen);
            }
        }
        
            
        if (window.addEventListener) {
        	window.addEventListener("message", handleMessage);
        } else {
        	window.attachEvent("onmessage", handleMessage);
        }
        
        function handleMessage(event){
            if(event && event.isTrusted && event.origin === pilllz_url){
                if(event.data === "pillz.saved"){
                    
                    // Check if Bootstrap's modal function is defined
                    if (typeof bootstrap !== 'undefined') {
                        PilllzBsModal.hide();
                    }else{
                        PilllzModal.remove();
                    }
                    
                    Iframe.src = Iframe.src+"?t=" + new Date().getTime();
                    let tempSrc = pilllz_url+`/avatar/`+user_id+`/svg`;
                    imageTag.src = tempSrc+`?refresh=`+ new Date().getTime();
                    
                    let allImages = document.getElementsByTagName('img');
                    for (var n = 0; n < allImages.length; n++) {
                        let currentSrc = allImages[n].getAttribute('src');
                        if (currentSrc.includes(tempSrc)) {
                            allImages[n].src = tempSrc+`?refresh=`+ new Date().getTime();
                        }
                    }

                }
            }
        }

        
    }
    
    function PilllzOpen(event) {
      event.preventDefault();
    
      // Check if Bootstrap's modal function is defined
      if (typeof bootstrap !== 'undefined') {

        // Use Bootstrap's modal, create if not exist
        if(PilllzBsModal === undefined){
            PilllzModal.classList.add('modal', 'fade');
            PilllzModal.setAttribute('tabindex', '-1');
            PilllzModal.setAttribute('role', 'dialog');
        
            var modalDialog = document.createElement('div');
            modalDialog.classList.add('modal-dialog', 'modal-xl');
            modalDialog.setAttribute('role', 'document');
        
            modalContent = document.createElement('div');
            modalContent.classList.add('modal-content');
        
            var modalBody = document.createElement('div');
            modalBody.classList.add('modal-body');
            
            modalBody.appendChild(Iframe);
            modalContent.appendChild(modalBody);
            modalDialog.appendChild(modalContent);
            PilllzModal.appendChild(modalDialog);
            PilllzBsModal = new bootstrap.Modal(PilllzModal);
        }
        
        PilllzBsModal.show();
      } else {
        // Use a custom modal
        PilllzModal.classList.add('pilllz-modal');
        PilllzModal.style.cursor = 'pointer';
        if(modalContent === undefined){
            modalContent = document.createElement('div');
            modalContent.classList.add('pilllz-modal-content');
            modalContent.appendChild(Iframe);
            PilllzModal.appendChild(modalContent);
            PilllzModal.addEventListener("click", function(ev) {
                ev.preventDefault();
                hideModal();
            });
        }
        document.body.appendChild(PilllzModal);
      }
    }

    function hideModal(){
        PilllzModal.classList.remove('pilllz-modal');
        PilllzModal.style.cursor = 'default';
    }
});