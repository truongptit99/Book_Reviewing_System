import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import I18n from './vendor/I18n';
window.I18n = I18n;

const echo = new Echo({
    broadcaster: 'pusher',
    key: '6a91eccd9a26a43ab637',
    cluster: 'ap1',
    forceTLS: true
});

Pusher.logToConsole = true;
let id = $('#user_id').val();
let translator = new I18n;

echo.private(`users.${id}`).notification((notification) => {
    let today = new Date();
    let date = today.getDate() + '-' + (today.getMonth() + 1) + '-' + today.getFullYear() + ' '
        + today.getHours() + ':' + today.getMinutes();
    let currentNotiCount = $('.notification-count').text();

    var newNotificationHtml = `
        <div class="dropdown-divider"></div>
        <a href="/users/${notification.user['id']}/?markRead=${notification.id}" class="dropdown-item">
            <i class="fas fa-user-friends mr-2"></i>
            <span>
                <b><i>${notification.user['username']}</i></b> ` +
                translator.trans('messages.followed-you') +
            `</span>
            <span class="float-right text-muted text-sm">${date}</span>
        </a>
    `;

    $('.menu-notification').prepend(newNotificationHtml);
    currentNotiCount++;
    $('.notification-count').text(currentNotiCount);
});
