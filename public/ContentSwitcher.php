<!doctype html>
<html lang="de">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="./styles/style_contentswitcher.css" />
  <title>Viewport</title>

</head>

<body>

  <header class="content-header">
    <div class="header-left">
      <a href="https://www.htlrennweg.at/" class="logo-link">
        <img src="images/logo.png" alt="Logo" class="logo">
      </a>
      <div class="brand">Schulmonitor</div>
    </div>
    <div class="header-right">
      <div class="current-time" id="currentTime">00:00</div>
    </div>
  </header>

  <div class="slides" id="slides">
    <div class="slides-inner" id="slidesInner">
    </div>
  </div>

  <!-- Instant Message Overlay -->
  <div class="instant-message-overlay" id="instantOverlay">
    <div class="instant-message-content" id="instantContent"></div>
  </div>

  <!--Here beginns the js sript-->

  <script>
    //++++++++++++++++++++ DAYTIME UPDATE ++++++++++++++++++++
    function updateTime() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      document.getElementById('currentTime').textContent = `${hours}:${minutes} Uhr`;
    }
    updateTime();
    setInterval(updateTime, 1000);

    //++++++++++++++++++++ DATA CRATION ++++++++++++++++++++

    let titles = []; // <<< ÄNDERUNG
    let texts = []; // <<< ÄNDERUNG
    let media = []; // <<< ÄNDERUNG
    let init = true;

    let lastSlidesJson = null;
    let lastTimeJson = null;


    //++++++++++++++++++++ TIME-CONTROL ++++++++++++++++++++

    //default time
    let slideDurationSeconds = 1;
    //instant time
    let instantDurationSeconds = 10;

    //++++++++++++++++++++ FETCH-CONTROL ++++++++++++++++++++   

    function IntervalFetch() {

    }


    function TimeFetch() {
      fetch('./admin.php', {
          headers: {
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(timeData => {
          const newTimeJson = JSON.stringify(timeData);


          // Prüfen auf Änderungen
          if (lastTimeJson && lastTimeJson !== newTimeJson) {
            console.log("Time Update erkannt! Seite reloadet...");
            location.reload();
            return;
          }

          lastTimeJson = newTimeJson;
          if (slideDurationSeconds === 1) {
            slideDurationSeconds = Number(timeData.MsgTime ?? 3);
            console.log("Msg Zeit beträgt: " + slideDurationSeconds + "s");
          }
        })
        .catch(err => console.error("Fehler beim Laden der Time-Daten:", err));
    }



    function SlideFetch() {
      fetch("./SlideGenTest.php")
        .then(res => res.json())
        .then(data => {
          const newSlidesJson = JSON.stringify(data);

          // Prüfen auf Änderungen
          if (lastSlidesJson && lastSlidesJson !== newSlidesJson) {
            console.log("Update erkannt! Seite reloadet...");
            location.reload();
            return;
          }

          lastSlidesJson = newSlidesJson;

          data.forEach(item => {
            titles.push(item.title);
            texts.push(item.text ?? "");
            if (!item.media || item.media.trim() === "") {
              media.push({
                instant: false
              });
            } else {
              media.push({
                type: item.type?.toLowerCase() === "video" ? "video" : "image",
                src: item.media,
                instant: false
              });
            }
          });

          // Slides initialisieren **nur einmal**
          if (init === true) {
            initSlides();
            console.log("INIT erkannt! Seite reloadet...");
            init = false;
          }
        })
        .catch(err => console.error("Fehler beim Slides-Fetch:", err));
    }
    IntervalFetch();
    TimeFetch();
    SlideFetch();

    setInterval(TimeFetch, 20000)
    setInterval(SlideFetch, 10000);


    //++++++++++++++++++++ Slide Creation ++++++++++++++++++++
    function initSlides() { // <<< ÄNDERUNG

      const slidesInner = document.getElementById('slidesInner');
      const n = titles.length;
      const slideData = []; //Regestry 

      for (let i = 0; i < n; i++) {
        const slide = document.createElement('section');
        slide.className = 'slide';
        slide.dataset.index = i;

        //++++++ TITLE CONTROL ++++++ 
        const h = document.createElement('h1');
        h.textContent = titles[i];
        slide.appendChild(h);

        //++++++ TEXT CONTROL ++++++
        if (texts[i] && texts[i].trim() !== "") {
          const isInstant = media[i]?.instant === true;

          // Add separator bar before text
          const separator = document.createElement('hr');
          separator.className = 'text-separator';
          slide.appendChild(separator);

          const d = document.createElement('div');
          d.className = 'text';
          if (isInstant) {
            d.classList.add('instant');
          }
          d.textContent = texts[i];
          slide.appendChild(d);
        }



        //++++++ MEDIA CONTROL ++++++
        let videoRef = null;

        if (media[i] !== null && media[i] !== undefined && (media[i].type === "video" || media[i].type === "image")) {
          const wrap = document.createElement('div');
          wrap.className = 'video-wrap';

          if (media[i]?.type === "video") {
            const video = document.createElement('video');
            video.src = media[i].src;
            video.preload = 'auto';
            video.muted = true;
            video.loop = false;
            video.playsInline = true;

            video.addEventListener("ended", () => {
              if (slideData[currentIndex]?.video === video) {
                goTo(currentIndex + 1);
              }
            });

            wrap.appendChild(video);
            videoRef = video;
          } else if (media[i]?.type === "image") {
            const img = document.createElement('img');
            img.src = media[i].src;
            img.alt = titles[i];
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "contain";
            img.draggable = false;
            wrap.appendChild(img);
          }

          slide.appendChild(wrap); //Activiates Media Container
        }

        slidesInner.appendChild(slide);
        slideData.push({
          slide,
          video: videoRef
        });
      }

      //++++++ SEPARATOR WIDTH ADJUSTMENT ++++++
      function adjustSeparators() {
        slideData.forEach(sd => {
          const slide = sd.slide;
          const titleEl = slide.querySelector('h1');
          const textEl = slide.querySelector('.text');
          const separator = slide.querySelector('.text-separator');

          if (separator && titleEl && textEl) {
            // Get the actual width of both elements
            const titleWidth = titleEl.scrollWidth;
            const textWidth = textEl.scrollWidth;
            const maxWidth = Math.max(titleWidth, textWidth);
            separator.style.width = maxWidth + 'px';
          }
        });
      }

      //++++++ TITLE FONT SIZE ADJUSTMENT ++++++
      function adjustTitleFontSizes() {
        slideData.forEach(sd => {
          const titleEl = sd.slide.querySelector('h1');
          if (titleEl) {
            const titleLength = titleEl.textContent.length;
            let fontSize;

            // Adjust font size based on character count
            if (titleLength <= 10) {
              fontSize = 'clamp(48px, 7vw, 72px)';
            } else if (titleLength <= 20) {
              fontSize = 'clamp(40px, 6vw, 60px)';
            } else if (titleLength <= 40) {
              fontSize = 'clamp(30px, 4.5vw, 48px)';
            } else {
              fontSize = 'clamp(20px, 3.5vw, 40px)';
            }

            titleEl.style.fontSize = fontSize;
          }
        });
      }

      // Adjust title font sizes after elements are rendered
      setTimeout(adjustTitleFontSizes, 100);

      // Re-adjust on window resize
      window.addEventListener('resize', adjustTitleFontSizes);

      // Adjust separators after a small delay to ensure elements are rendered
      setTimeout(adjustSeparators, 100);

      // Re-adjust on window resize
      window.addEventListener('resize', adjustSeparators);

      //++++++ INSTANT MESSAGE OVERLAY CONTROL ++++++
      const instantOverlay = document.getElementById('instantOverlay');
      const instantContent = document.getElementById('instantContent');
      let instantTimer = null;

      function updateInstantMessageOverlay(index) {
        // Clear any existing instant message timer
        clearTimeout(instantTimer);

        if (media[index]?.instant === true && texts[index]) {
          // Show overlay with instant message
          instantContent.textContent = texts[index];
          instantOverlay.classList.add('active');

          // Auto-hide after instantDurationSeconds
          instantTimer = setTimeout(() => {
            instantOverlay.classList.remove('active');
          }, Number(instantDurationSeconds) * 1000);
        } else {
          // Hide overlay for non-instant slides
          instantOverlay.classList.remove('active');
        }
      }

      //++++++ TIME CONTROL ++++++
      function getDurationMs(i) {
        // instant
        if (media[i].instant === true) {
          return Number(instantDurationSeconds) * 1000;
        }

        // global
        return Number(slideDurationSeconds) * 1000;
      }

      //++++++ SLIDE CONTROL ++++++
      let currentIndex = 0;
      let autoTimer = null;

      function goTo(index) {
        if (index < 0) index = slideData.length - 1;
        if (index >= slideData.length) index = 0;

        currentIndex = index;

        const y = -index * window.innerHeight;
        slidesInner.style.transform = `translateY(${y}px)`;

        // Update instant message overlay
        updateInstantMessageOverlay(index);

        //Stops all videos (again) to play the selected one (-> idiotic)
        slideData.forEach((sd, idx) => {
          if (sd.video) {
            try {
              if (idx === index) {
                sd.video.currentTime = 0;
                const playPromise = sd.video.play();
                if (playPromise && typeof playPromise.then === 'function') {
                  playPromise.catch(() => {});
                }
              } else {
                sd.video.pause();
              }
            } catch (e) {
              console.warn('Video play/pause Fehler', e);
            }
          }
        });

        scheduleNext();
      }


      //++++++++ Timer Managment ++++++++++ --> do not touch
      function scheduleNext() {
        clearTimeout(autoTimer);

        const sd = slideData[currentIndex];

        //Video Managment
        if (sd.video) return;

        //General Managment
        const ms = getDurationMs(currentIndex);
        autoTimer = setTimeout(() => goTo(currentIndex + 1), ms);
      }



      //++++++++ Window Managment ++++++++++ --> please touch

      /* Bei Größenänderung neu-positionieren (z.B. Fenster-Resize) */
      window.addEventListener('resize', () => {
        // setze transform neu (weil 100vh könnte sich ändern)
        const y = -currentIndex * window.innerHeight;
        slidesInner.style.transform = `translateY(${y}px)`;
      });


      //++++++++ Transision Managment ++++++++++
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          clearTimeout(autoTimer);
          //stops all vids
          slideData.forEach(sd => sd.video && sd.video.pause());
        } else {
          //play current video und schedule next
          const sd = slideData[currentIndex];
          if (sd && sd.video) {
            sd.video.play().catch(() => {});
          }
          scheduleNext();
        }
      });

      //Initializer
      goTo(0);

      window.goToSlide = (i) => goTo(i);
      window.getSlideCount = () => slideData.length;
    }
  </script>
</body>

</html>