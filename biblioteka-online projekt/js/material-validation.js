
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#materialForm');

    if (!form || !window.JustValidate) {
        return;
    }

    const validation = new window.JustValidate(
        '#materialForm',
        {
            validateBeforeSubmitting: true
        }
    );

    validation
        .addField('#title', [
            {
                rule: 'required',
                errorMessage: 'Введите название материала'
            },
            {
                rule: 'maxLength',
                value: 200,
                errorMessage: 'Максимум 200 символов'
            }
        ])
        .addField('#author', [
            {
                rule: 'required',
                errorMessage: 'Введите автора или организацию'
            },
            {
                rule: 'maxLength',
                value: 150,
                errorMessage: 'Максимум 150 символов'
            }
        ])
        .addField('#description', [
            {
                rule: 'required',
                errorMessage: 'Введите описание материала'
            },
            {
                rule: 'maxLength',
                value: 5000,
                errorMessage: 'Слишком длинное описание'
            }
        ])
        .addField('#category_id', [
            {
                rule: 'required',
                errorMessage: 'Выберите категорию'
            }
        ])
        .addField('#pdf', [
            {
                rule: 'minFilesCount',
                value: 1,
                errorMessage: 'Выберите PDF-файл'
            },
            {
                rule: 'files',
                value: {
                    files: {
                        extensions: ['pdf'],
                        maxSize: 20 * 1024 * 1024,
                        minSize: 1,
                        types: ['application/pdf']
                    }
                },
                errorMessage:
                    'Разрешён только PDF размером до 20 МБ'
            }
        ])
        .onSuccess((event) => {
            event.currentTarget.submit();
        });
});
