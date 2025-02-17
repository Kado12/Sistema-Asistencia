function formatDoc(cmd,value=null){
    if(value){
        document.execCommand(cmd,false,value);
    }else{
        document.execCommand(cmd);
    }
}
function addLink(){
    const url = prompt('Insertar url');
    formatDoc('createLink',url);
}

const content = document.getElementById('content');
content.addEventListener('mouseenter',function(){
    const a = content.querySelectorAll('a');
    a.forEach(item => {
        item.addEventListener('mouseenter',function(){
            content.setAttribute('contenteditable',false);
            item.target = '_blank'
        })
        item.addEventListener('mouseleave',function(){
            content.setAttribute('contenteditable',true)
        })
    })
})

const showCode = document.getElementById('show-code');
let active = false;
showCode.addEventListener('click',function(){
    showCode.dataset.active = !active;
    active = !active
    if(active){
        content.textContent = content.innerHTML;
        content.setAttribute('contenteditable',false);
    }else{
        content.innerHTML = content.textContent;
        content.setAttribute('contenteditable',true)
    }
})

