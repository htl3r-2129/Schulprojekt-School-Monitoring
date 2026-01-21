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

  <!-- Instant message overlay removed -->

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

    let titles = []; // 
    let texts = [];
    let media = []; 
    let init = true;

    let lastSlidesJson = null;
    let lastTimeJson = null;

    //++++++++++++++++++++ TIME-CONTROL ++++++++++++++++++++

    //default time
    let slideDurationSeconds = 1;
    // instant-message feature removed

    //++++++++++++++++++++ FETCH-CONTROL ++++++++++++++++++++   


    function TimeFetch() {
      fetch('settings.json', { cache: 'no-store' })
        .then(res => res.json())
        .then(settings => {
          const newTimeJson = JSON.stringify(settings);

    
          console.log(newTimeJson);
          if (lastTimeJson && lastTimeJson !== newTimeJson) {
            console.log("Time Update erkannt! Seite reloadet...");
            location.reload();
            return;
          }

          //console.log("Msg Zeit beträgt: " + slideDurationSeconds + "s"); --> Debugging
          lastTimeJson = newTimeJson;
          const fetchedTime = Number(settings.bilderzeit?.replace(/\D/g, ''));
          if (slideDurationSeconds === 1) {
            slideDurationSeconds = Number(fetchedTime ?? 3);
            console.log("Msg Zeit beträgt: " + slideDurationSeconds + "s");
          }
        })
        .catch(err => console.error("Fehler beim Laden der Time-Daten:", err));
    }



    function SlideFetch() {
      fetch('queue.json', { cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
          const newSlidesJson = JSON.stringify(data);

          //console.log(newSlidesJson) --> Debug
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
              media.push({});
            } else {
              media.push({
                type: item.type?.toLowerCase() === "video" ? "video" : "image",
                src: item.media
              });
            }
          });

          if (init === true) {
            initSlides();
            console.log("INIT erkannt! Seite reloadet...");
            init = false;
          }
        })
        .catch(err => console.error("Fehler beim Slides-Fetch:", err));
    }
  

    TimeFetch();
    SlideFetch();

    setInterval(TimeFetch, 1000);
    setInterval(SlideFetch, 10000);


    //++++++++++++++++++++ Slide Creation ++++++++++++++++++++
    function initSlides() { 

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
        if (texts[i] && texts[i].trim() !== "" || (media[i] !== null && media[i] !== undefined && (media[i].type === "video" || media[i].type === "image"))) {
          // instant messaging removed; treat all slides as normal
          const isInstant = false;

          // Add separator bar before text
          const separator = document.createElement('hr');
          separator.className = 'text-separator';
          slide.appendChild(separator);

          if (texts[i] && texts[i].trim() !== "") {
            const d = document.createElement('div');
            d.className = 'text';
            // no instant class applied
            d.textContent = texts[i];
            slide.appendChild(d);
          }
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

          if (separator && titleEl) {
            const titleWidth = titleEl.scrollWidth;
            let maxWidth = titleWidth;
            if (textEl) {
              const textWidth = textEl.scrollWidth;
              maxWidth = Math.max(titleWidth, textWidth);
            }
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

      // Instant message overlay/logic removed

      //++++++ TIME CONTROL ++++++
      function getDurationMs(i) {
        // global duration for all slides
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

        // instant message feature removed

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