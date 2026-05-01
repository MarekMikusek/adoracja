@extends('layouts.app')

@section('styles')
    <style>
        .instruction-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 40vh;
            text-align: center;
            gap: 20px;
            padding-bottom: 50px;
        }

        .instruction-container h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #333;
        }

        .video-thumbnail {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }

        .video-thumbnail:hover {
            transform: scale(1.03);
        }

        /* Sekcja FAQ */
        .faq-section {
            max-width: 800px;
            margin: 40px auto;
            text-align: left;
        }

        .faq-section h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .faq-item {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }

        .faq-question {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .faq-answer {
            line-height: 1.6;
            color: #555;
        }

        .faq-image-container {
            margin-top: 15px;
        }

        .faq-img {
            width: 200px;
            /* Rozmiar miniaturki */
            cursor: pointer;
            border-radius: 5px;
            transition: opacity 0.3s;
        }

        .faq-img:hover {
            opacity: 0.8;
        }

        /* Modal do powiększania zdjęć */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="instruction-container">
            <h1>Instrukcja do aplikacji:</h1>
            <a href="https://www.youtube.com/watch?v=1pBjEkAdZz8" target="_blank">
                <img src="{{ asset('images/apka_ChJZ_www.jpg') }}" alt="Film na YouTube" class="video-thumbnail">
            </a>
        </div>

        <hr>

        <div class="faq-section">
            <h2>Często zadawane pytania (FAQ)</h2>

            <div class="faq-item">
                <div class="faq-question">Jak mogę się zajestrować?</div>
                <div class="faq-answer">
                    Wejdź na <a href="https://adoracja.chjz.pl/register">stronę rejestracji</a>, wpisz swoje dane,
                    przeczytaj i potwierdź informację RODO. Kliknij na 'Utwórz konto' i gotowe 🙂.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Czy muszę podać prawdziwe dane?</div>
                <div class="faq-answer">
                    <p>Twoje dane są widoczne tylko dla koordynatorów. Podaj tyle informacji, żeby zespół koordynujący
                        wiedział kto jest zapisany na posługę i miał z Tobą kontakt. </p>
                    <p>Prosimy o minimum zaufania, bez niego będzie trudno w odpowiedzialny sposób prowadzić to dzieło.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Czy muszę się deklarować cotygodniową posługę?</div>
                <div class="faq-answer">
                    <p>Nie musisz.🙂 Poszukujemy osób, które będą gotowe posługiwać regularnie, ale zapraszamy też osoby,
                        które nie są na to gotowe.</p>
                    <p>Zarejestruj się, jeśli chcesz pozostać adoratorem okazjonalnym to prosimy o zapisanie się wtedy kiedy
                        Ci pasuje.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Dlaczego powinenem się zajestrować i zapisywać na adorację?</div>
                <div class="faq-answer">
                    <p>Naszą wspólną odpowiedzialnością jest zadbanie żeby cały cza ktoś był z Panem Jezusem. Na tm polega
                        wieczysta adoracja.</p>
                    <p>Ta strona internetowa ma pomagać w zapewnieniu stałej obecności osób modlących się w kaplicy
                        adoracji. Zapisanie czy to na posługę stałą czy okazjonalną bardzo ułatwi (niełatwą) pracę
                        koordynatorów.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Czy mogę zapisać się na adorację, mimo że jestem z innej parafii?</div>
                <div class="faq-answer">
                    <p>Jak najbardziej, kaplica jest otwarta dla każdego.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Czy w godzinach wieczornych/nocnych jest udostępniony parking?</div>
                <div class="faq-answer">
                    <p>Tak, parking przykościelny jest dostępny przez całą dobę.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Czy w kaplicy jest dostępna lampka do czytania?</div>
                <div class="faq-answer">
                    <p>Tak, są dostępne dwa dedykowane miejsca z lampkami.</p>
                </div>
            </div>

        </div>
    @endsection

    @section('scripts')
        <script>
            function openModal(src) {
                const modal = document.getElementById("imageModal");
                const modalImg = document.getElementById("imgFullSize");
                modal.style.display = "flex";
                modalImg.src = src;
            }

            function closeModal() {
                const modal = document.getElementById("imageModal");
                modal.style.display = "none";
            }

            // Zamknij modal po naciśnięciu klawisza Esc
            document.addEventListener('keydown', function(event) {
                if (event.key === "Escape") {
                    closeModal();
                }
            });
        </script>
    @endsection
