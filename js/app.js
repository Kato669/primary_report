const name_1 = document.getElementById('name');
const icon = document.getElementById('icon');
const leftBar = document.getElementById('left-bar');
const rightBar = document.getElementById('right-bar');
const sideTexts = document.querySelectorAll('.side-text');
const toggleBar = document.querySelector('.toggleBar');

icon.addEventListener('click',()=>{
    name_1.classList.toggle('active');
    leftBar.classList.toggle('active');
    rightBar.classList.toggle('active')
    // sideText.classList.toggle('active');
    sideTexts.forEach(sideText =>{
        sideText.classList.toggle('active');
    })

})

new DataTable('#example');
toggleBar.addEventListener('click', ()=>{
    leftBar.classList.toggle('show');
})

