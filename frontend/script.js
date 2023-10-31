
// document.addEventListener('DOMContentLoaded', function () {
//     const registerForm = document.getElementById('register-form');
//     const registerMessage = document.getElementById('register-message');

//     registerForm.addEventListener('submit', function (e) {
//         e.preventDefault();

//         const username = document.getElementById('username').value;
//         const email = document.getElementById('email').value;
//         const password = document.getElementById('password').value;

//         // バックエンドにユーザー登録データを送信するコードを追加
//         // 以下は簡単な例です。実際にはAjaxなどを使用してバックエンドと通信する必要があります。

//         fetch('register.php', {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//             },
//             body: JSON.stringify({ username, email, password }),
//         })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     registerMessage.textContent = 'ユーザー登録が成功しました。';
//                 } else {
//                     registerMessage.textContent = 'ユーザー登録に失敗しました。';
//                 }
//             })
//             .catch(error => {
//                 console.error('Error:', error);
//                 registerMessage.textContent = 'エラーが発生しました。';
//             });
//     });
// });