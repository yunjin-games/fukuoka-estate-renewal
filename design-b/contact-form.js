// 문의 폼을 contact-send.php 로 보낸다. 공개 시안(github.io)에는 PHP 가 없어 보내지 않는다.
document.querySelectorAll('[data-contact-form]').forEach(function (form) {
  var done = form.querySelector('[data-form-done]');
  var fail = form.querySelector('[data-form-error]');
  var button = form.querySelector('button[type="submit"]');

  function show(el) {
    el.classList.remove('hidden');
    el.classList.add('flex');
  }

  function setBusy(busy) {
    button.disabled = busy;
    button.classList.toggle('opacity-60', busy);
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    fail.classList.add('hidden');
    fail.classList.remove('flex');

    if (location.hostname.endsWith('github.io')) {
      done.querySelector('p').textContent = 'デザイン確認用のページのため、送信はされません。';
      show(done);
      return;
    }

    setBusy(true);
    fetch('contact-send.php', { method: 'POST', body: new FormData(form) })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        if (!result.ok) throw new Error(result.error);
        form.reset();
        show(done);
      })
      .catch(function () {
        setBusy(false);
        show(fail);
      });
  });
});
