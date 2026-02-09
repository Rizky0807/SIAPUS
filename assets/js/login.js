
const container = document.getElementById('container');
const adminBtn  = document.getElementById('admin');
const userBtn = document.getElementById('user');

if(adminBtn && userBtn){
    adminBtn.addEventListener('click', () => {
        container.classList.remove("active");
    });

    userBtn.addEventListener('click', () => {
        container.classList.add("active");
    })
}
