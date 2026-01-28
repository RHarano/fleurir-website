/**
 * FLEURIR - Password Protection
 */

(function() {
    const PASSWORD = 'fleurir';
    const AUTH_KEY = 'fleurir_auth';

    // Check if already authenticated
    if (sessionStorage.getItem(AUTH_KEY) === 'true') {
        return;
    }

    // Create overlay
    const overlay = document.createElement('div');
    overlay.id = 'auth-overlay';
    overlay.innerHTML = `
        <div class="auth-container">
            <h1>FLEURIR</h1>
            <p>パスワードを入力してください</p>
            <form id="auth-form">
                <input type="password" id="auth-password" placeholder="パスワード" autocomplete="off">
                <button type="submit">Enter</button>
            </form>
            <p id="auth-error" style="display: none; color: #c00; margin-top: 10px;">パスワードが正しくありません</p>
        </div>
    `;

    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        #auth-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-container {
            text-align: center;
            padding: 40px;
        }
        .auth-container h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            color: #9F886E;
            margin-bottom: 10px;
            letter-spacing: 0.2em;
        }
        .auth-container p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        #auth-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 250px;
            margin: 0 auto;
        }
        #auth-password {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
        }
        #auth-password:focus {
            outline: none;
            border-color: #9F886E;
        }
        #auth-form button {
            padding: 12px 30px;
            background: #9F886E;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        #auth-form button:hover {
            background: #8a7560;
        }
    `;

    document.head.appendChild(style);
    document.body.appendChild(overlay);

    // Handle form submission
    document.getElementById('auth-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('auth-password').value;

        if (input === PASSWORD) {
            sessionStorage.setItem(AUTH_KEY, 'true');
            overlay.remove();
            style.remove();
        } else {
            document.getElementById('auth-error').style.display = 'block';
            document.getElementById('auth-password').value = '';
        }
    });
})();
