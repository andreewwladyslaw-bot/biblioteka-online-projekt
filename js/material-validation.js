
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
                errorMessage: 'Wpisz tytuł materiału'
            },
            {
                rule: 'maxLength',
                value: 200,
                errorMessage: 'Maksymalnie 200 znaków'
            }
        ])
        .addField('#author', [
            {
                rule: 'required',
                errorMessage: 'Wpisz autora lub nazwę organizacji'
            },
            {
                rule: 'maxLength',
                value: 150,
                errorMessage: 'Maksymalnie 150 znaków'
            }
        ])
        .addField('#description', [
            {
                rule: 'required',
                errorMessage: 'Wpisz opis materiału'
            },
            {
                rule: 'maxLength',
                value: 5000,
                errorMessage: 'Opis jest zbyt długi'
            }
        ])
        .addField('#category_id', [
            {
                rule: 'required',
                errorMessage: 'Wybierz kategorię'
            }
        ])
        .addField('#pdf', [
            {
                rule: 'minFilesCount',
                value: 1,
                errorMessage: 'Wybierz plik PDF'
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
                    'Dozwolony jest wyłącznie plik PDF o rozmiarze do 20 MB'
            }
        ])
        .onSuccess((event) => {
            event.currentTarget.submit();
        });
});
