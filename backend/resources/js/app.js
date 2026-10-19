// Fields marked data-digits-only (e.g. contact numbers) accept digits only:
// other typed characters are blocked, and pasted or autofilled text is reduced to its digits.
document.addEventListener('beforeinput', (event) => {
    const field = event.target;
    if (field.matches?.('input[data-digits-only]') && event.inputType === 'insertText' && /\D/.test(event.data ?? '')) {
        event.preventDefault();
    }
});

document.addEventListener('input', (event) => {
    const field = event.target;
    if (!field.matches?.('input[data-digits-only]')) return;

    const digits = field.value.replace(/\D/g, '').slice(0, field.maxLength > 0 ? field.maxLength : undefined);
    if (digits !== field.value) field.value = digits;
});
