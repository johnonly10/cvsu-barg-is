
// Update notification count dynamically
function updateNotificationCount(unreadCount) {
    console.log('Updating notification count. Unread Count:', unreadCount);

    if (unreadCount > 0) {
        $('.notification-count').text(unreadCount).show();
    } else {
        $('.notification-count').text('').hide();
    }
}


function updateNotificationCountAndList() {
  $.ajax({
      url: '/notifications/latest',
      method: 'GET',
      success: function(response) {

        updateNotificationCount(response.unreadCount);
          // Update the notification count
          if (response.unreadCount > 0) {
              $('.notification-count').text(response.unreadCount).show();
          } else {
              $('.notification-count').hide();
          }

          // Update the notification list dynamically
          const notificationList = $('#notifications-list');
          const selectAllSection = notificationList.find('.select-all')
      .detach(); // Preserve "Select All"
          notificationList.empty().append(selectAllSection);

          // Populate with new notifications
          if (response.notifications.length > 0) {
              response.notifications.forEach(notification => {
                  const notificationHtml = `
                  <li class="message-item notification-item ${notification.read_at ? 'read' : 'unread'}"
                      data-notification-id="${notification.id}">
                      <input type="checkbox" class="notification-checkbox" />
                      <div class="image">
                          <i class="${notification.icon}"></i>
                      </div>
                      <div class="notification-content">
                          <a href="${notification.redirect_route || '#'}" class="notification-link">
                              <div class="body-title-2 ${notification.read_at ? 'text-muted' : 'text-warning'}">
                                  ${notification.message}
                              </div>
                          </a>
                          <div class="text-tiny ${notification.read_at ? 'text-muted' : ''}">
                              ${notification.created_at}
                          </div>
                      </div>
                  </li>`;
                  notificationList.append(notificationHtml);
              });
          } else {
              notificationList.append(
                  '<li class="no-notifications"><div class="text-tiny">No unread notifications</div></li>'
                  );
          }
      },
      error: function(xhr) {
          console.error('Error fetching notifications:', xhr.responseText);
      },
  });
}