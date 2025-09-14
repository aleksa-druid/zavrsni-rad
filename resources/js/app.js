import './bootstrap';

// --- Livewire <-> Echo: send socket id with requests so .toOthers() works ---
document.addEventListener('livewire:load', () => {
  window.Livewire.hook('request', ({ options }) => {
    const sid = window.Echo?.socketId?.();
    if (sid) options.headers['X-Socket-Id'] = sid;
  });
});

// --- Option B: manually (re)bind Reverb private channel on room change ---
document.addEventListener('bind-echo-chat', (e) => {
  const { roomId, componentId } = e.detail || {};

  if (window.__chatChannel) {
    try { window.__chatChannel.unsubscribe(); } catch (_) {}
    window.__chatChannel = null;
  }

  if (!window.Echo || !roomId || !componentId) return;

  window.__chatChannel = window.Echo.private(`chat.${roomId}`)
    .listen('.message.sent', (payload) => {
      const comp = window.Livewire?.find?.(componentId);
      if (comp) comp.call('receive', payload);
    });
});

// --- UX helpers: clear input + scroll after DOM patch ---
document.addEventListener('clear-message-input', () => {
  const el = document.getElementById('messageInput');
  if (!el) return;
  el.value = '';
  el.dispatchEvent(new Event('input', { bubbles: true }));
});

document.addEventListener('scroll-to-bottom', () => {
  const go = () => {
    const box = document.getElementById('messagesBox');
    if (box) box.scrollTop = box.scrollHeight;
  };
  requestAnimationFrame(() => setTimeout(go, 0));
});
