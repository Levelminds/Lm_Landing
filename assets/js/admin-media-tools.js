(function () {
  const forms = document.querySelectorAll('[data-blog-form]');
  if (!forms.length) {
    return;
  }

  const modalEl = document.getElementById('mediaCropModal');
  if (!modalEl || typeof bootstrap === 'undefined') {
    return;
  }

  const modal = new bootstrap.Modal(modalEl, { backdrop: 'static' });
  const previewImage = modalEl.querySelector('[data-cropper-target]');
  const ratioSelect = modalEl.querySelector('[data-aspect-select]');
  const widthInput = modalEl.querySelector('[data-output-width]');
  const heightInput = modalEl.querySelector('[data-output-height]');
  const statusText = modalEl.querySelector('[data-processing-status]');
  const errorBox = modalEl.querySelector('[data-processing-error]');
  const spinner = modalEl.querySelector('[data-processing-indicator]');
  const confirmBtn = modalEl.querySelector('[data-apply-crop]');
  const originalSizeLabel = modalEl.querySelector('[data-original-size]');
  const hintText = modalEl.querySelector('[data-processing-hint]');

  const ratioMap = {
    original: null,
    free: NaN,
    '16:9': 16 / 9,
    '4:3': 4 / 3,
    '1:1': 1,
    '9:16': 9 / 16
  };

  let cropper = null;
  let currentInput = null;
  let currentFile = null;
  let currentType = 'image';
  let naturalWidth = 0;
  let naturalHeight = 0;
  let ffmpeg = null;
  let ffmpegLoading = false;
  const previewUrls = new Set();

  function clearPreviewUrls() {
    previewUrls.forEach((value) => URL.revokeObjectURL(value));
    previewUrls.clear();
  }

  function registerPreviewUrl(url) {
    previewUrls.add(url);
  }

  function resetModalState() {
    statusText.textContent = '';
    errorBox.classList.add('d-none');
    errorBox.textContent = '';
    spinner.classList.add('d-none');
    confirmBtn.disabled = false;
    if (hintText) {
      hintText.classList.toggle('d-none', currentType !== 'video');
    }
  }

  function updateDimensionInputs(cropData) {
    if (!cropData) {
      cropData = cropper ? cropper.getData(true) : null;
    }

    if (!cropData) {
      return;
    }

    const selectedRatio = ratioMap[ratioSelect.value];
    if (!Number.isFinite(selectedRatio) || selectedRatio <= 0) {
      widthInput.value = Math.round(cropData.width);
      heightInput.value = Math.round(cropData.height);
      return;
    }

    const width = Math.min(Math.round(cropData.width), naturalWidth);
    const height = Math.round(width / selectedRatio);

    if (height > naturalHeight) {
      const adjustedHeight = Math.min(Math.round(cropData.height), naturalHeight);
      widthInput.value = Math.round(adjustedHeight * selectedRatio);
      heightInput.value = adjustedHeight;
    } else {
      widthInput.value = width;
      heightInput.value = height;
    }
  }

  function bindCropper(aspectRatio) {
    if (cropper) {
      cropper.destroy();
      cropper = null;
    }

    cropper = new Cropper(previewImage, {
      aspectRatio: aspectRatio,
      viewMode: 2,
      autoCropArea: 1,
      movable: true,
      scalable: true,
      zoomOnWheel: true,
      ready() {
        updateDimensionInputs();
      },
      crop() {
        updateDimensionInputs();
      }
    });
  }

  function getOutputDimensions() {
    let width = parseInt(widthInput.value, 10);
    let height = parseInt(heightInput.value, 10);

    if (!Number.isFinite(width) || width <= 0) {
      width = naturalWidth;
    }
    if (!Number.isFinite(height) || height <= 0) {
      height = naturalHeight;
    }

    const ratio = ratioMap[ratioSelect.value];
    if (Number.isFinite(ratio) && ratio > 0) {
      height = Math.round(width / ratio);
    }

    width = Math.min(width, naturalWidth);
    height = Math.min(height, naturalHeight);

    return { width: Math.max(1, width), height: Math.max(1, height) };
  }

  function updatePreviewContainer(file, input) {
    const previewWrapper = input.closest('[data-media-field]')?.querySelector('[data-media-preview]');
    if (!previewWrapper) {
      return;
    }

    clearPreviewUrls();
    const url = URL.createObjectURL(file);
    registerPreviewUrl(url);

    let markup = '';
    if (currentType === 'video') {
      markup = `<video src="${url}" controls class="rounded border w-100" style="max-height: 220px; object-fit: cover;"></video>`;
    } else {
      markup = `<img src="${url}" alt="Processed preview" class="rounded border w-100" style="max-height: 220px; object-fit: cover;">`;
    }

    previewWrapper.innerHTML = markup;
  }

  function deriveFileName(original, extension) {
    const stem = original.name ? original.name.replace(/\.[^.]+$/, '') : 'media';
    return `${stem}-processed.${extension}`;
  }

  function handleImageProcessing() {
    if (!cropper) {
      return;
    }

    confirmBtn.disabled = true;
    spinner.classList.remove('d-none');
    statusText.textContent = 'Preparing cropped image…';

    const { width, height } = getOutputDimensions();
    const canvas = cropper.getCroppedCanvas({ width, height, imageSmoothingQuality: 'high' });
    if (!canvas) {
      statusText.textContent = '';
      spinner.classList.add('d-none');
      confirmBtn.disabled = false;
      errorBox.textContent = 'Unable to prepare the cropped image. Please try another selection.';
      errorBox.classList.remove('d-none');
      return;
    }

    canvas.toBlob((blob) => {
      if (!blob) {
        statusText.textContent = '';
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        errorBox.textContent = 'Unable to export the cropped image. Please try again.';
        errorBox.classList.remove('d-none');
        return;
      }

      const outputExt = blob.type.split('/').pop() || 'png';
      const file = new File([blob], deriveFileName(currentFile, outputExt), { type: blob.type });
      const transfer = new DataTransfer();
      transfer.items.add(file);
      currentInput.files = transfer.files;
      updatePreviewContainer(file, currentInput);
      statusText.textContent = 'Image ready.';
      setTimeout(() => {
        modal.hide();
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        statusText.textContent = '';
      }, 300);
    }, currentFile.type || 'image/png', 0.92);
  }

  async function ensureFfmpegLoaded() {
    if (ffmpeg) {
      return ffmpeg;
    }
    if (ffmpegLoading) {
      return new Promise((resolve) => {
        const interval = setInterval(() => {
          if (ffmpeg) {
            clearInterval(interval);
            resolve(ffmpeg);
          }
        }, 150);
      });
    }

    if (!window.FFmpeg || !window.FFmpeg.createFFmpeg || !window.FFmpeg.fetchFile) {
      throw new Error('Video processing tools are unavailable.');
    }

    ffmpegLoading = true;
    ffmpeg = window.FFmpeg.createFFmpeg({ log: false });
    await ffmpeg.load();
    ffmpegLoading = false;
    return ffmpeg;
  }

  async function handleVideoProcessing() {
    if (!cropper) {
      return;
    }

    confirmBtn.disabled = true;
    spinner.classList.remove('d-none');
    statusText.textContent = 'Processing video… this may take a moment.';
    errorBox.classList.add('d-none');

    try {
      const ffmpegInstance = await ensureFfmpegLoaded();
      const cropData = cropper.getData(true);
      const { width, height } = getOutputDimensions();

      const cropWidth = Math.max(1, Math.round(cropData.width));
      const cropHeight = Math.max(1, Math.round(cropData.height));
      const cropX = Math.max(0, Math.round(cropData.x));
      const cropY = Math.max(0, Math.round(cropData.y));
      const scaleWidth = Math.max(1, Math.round(width));
      const scaleHeight = Math.max(1, Math.round(height));

      const inputName = `input_${Date.now()}`;
      const outputName = `output_${Date.now()}.mp4`;

      ffmpegInstance.FS('writeFile', inputName, await window.FFmpeg.fetchFile(currentFile));

      const filter = `crop=${cropWidth}:${cropHeight}:${cropX}:${cropY},scale=${scaleWidth}:${scaleHeight}`;
      try {
        await ffmpegInstance.run('-i', inputName, '-vf', filter, '-c:a', 'copy', outputName);
      } catch (primaryError) {
        await ffmpegInstance.run(
          '-i',
          inputName,
          '-vf',
          filter,
          '-c:v',
          'libx264',
          '-preset',
          'fast',
          '-crf',
          '22',
          '-c:a',
          'aac',
          '-b:a',
          '128k',
          outputName
        );
      }

      const data = ffmpegInstance.FS('readFile', outputName);
      ffmpegInstance.FS('unlink', inputName);
      ffmpegInstance.FS('unlink', outputName);

      const blob = new Blob([data.buffer], { type: 'video/mp4' });
      const file = new File([blob], deriveFileName(currentFile, 'mp4'), { type: 'video/mp4' });
      const transfer = new DataTransfer();
      transfer.items.add(file);
      currentInput.files = transfer.files;
      updatePreviewContainer(file, currentInput);
      statusText.textContent = 'Video processed successfully.';
      setTimeout(() => {
        modal.hide();
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        statusText.textContent = '';
      }, 500);
    } catch (error) {
      spinner.classList.add('d-none');
      confirmBtn.disabled = false;
      statusText.textContent = '';
      errorBox.textContent = error.message || 'Unable to process the video. Please try again with a different file or crop.';
      errorBox.classList.remove('d-none');
    }
  }

  ratioSelect.addEventListener('change', () => {
    const ratio = ratioMap[ratioSelect.value];
    if (!cropper) {
      return;
    }
    if (ratio === null) {
      cropper.setAspectRatio(naturalWidth && naturalHeight ? naturalWidth / naturalHeight : NaN);
    } else {
      cropper.setAspectRatio(ratio);
    }
    updateDimensionInputs();
  });

  widthInput.addEventListener('input', () => {
    if (!cropper) {
      return;
    }
    const ratio = ratioMap[ratioSelect.value];
    if (Number.isFinite(ratio) && ratio > 0) {
      heightInput.value = Math.round(Math.max(1, parseInt(widthInput.value, 10) || naturalWidth) / ratio);
    }
  });

  heightInput.addEventListener('input', () => {
    if (!cropper) {
      return;
    }
    const ratio = ratioMap[ratioSelect.value];
    if (Number.isFinite(ratio) && ratio > 0) {
      widthInput.value = Math.round(Math.max(1, parseInt(heightInput.value, 10) || naturalHeight) * ratio);
    }
  });

  modalEl.addEventListener('hidden.bs.modal', () => {
    if (cropper) {
      cropper.destroy();
      cropper = null;
    }
    previewImage.src = '';
    currentFile = null;
    currentInput = null;
    currentType = 'image';
    naturalWidth = 0;
    naturalHeight = 0;
    clearPreviewUrls();
  });

  confirmBtn.addEventListener('click', (event) => {
    event.preventDefault();
    if (!currentInput || !currentFile || !cropper) {
      return;
    }
    if (currentType === 'video') {
      handleVideoProcessing();
    } else {
      handleImageProcessing();
    }
  });

  async function prepareVideoPreview(file) {
    return new Promise((resolve, reject) => {
      const video = document.createElement('video');
      video.preload = 'auto';
      video.muted = true;
      video.src = URL.createObjectURL(file);
      const cleanup = () => {
        URL.revokeObjectURL(video.src);
      };

      const captureFrame = () => {
        const canvas = document.createElement('canvas');
        const width = video.videoWidth || 1280;
        const height = video.videoHeight || 720;
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, width, height);
        cleanup();
        resolve({
          url: canvas.toDataURL('image/png'),
          dimensions: { width, height }
        });
      };

      video.addEventListener('loadedmetadata', () => {
        if (!video.duration || !Number.isFinite(video.duration)) {
          captureFrame();
          return;
        }
        const targetTime = Math.min(0.1, Math.max(0, video.duration - 0.1));
        const handleSeeked = () => {
          video.removeEventListener('seeked', handleSeeked);
          captureFrame();
        };
        video.addEventListener('seeked', handleSeeked);
        try {
          video.currentTime = targetTime;
        } catch (error) {
          handleSeeked();
        }
      });

      video.addEventListener('loadeddata', () => {
        if (!video.duration) {
          captureFrame();
        }
      });

      video.addEventListener('error', () => {
        cleanup();
        reject(new Error('Unable to preview this video. Please choose a different file.'));
      });
    });
  }

  function attachHandlers(form) {
    const fileInput = form.querySelector('[data-media-input]');
    const mediaTypeSelect = form.querySelector('[name="media_type"]');
    const adjustButton = form.querySelector('[data-open-crop]');
    const urlField = form.querySelector('[name="media_url"]');

    if (!fileInput) {
      return;
    }

    fileInput.addEventListener('change', async (event) => {
      const [file] = event.target.files;
      if (!file) {
        return;
      }

      currentInput = fileInput;
      currentFile = file;
      const type = (mediaTypeSelect?.value === 'video' || file.type.startsWith('video')) ? 'video' : 'image';
      currentType = type;
      resetModalState();
      ratioSelect.value = type === 'video' ? '16:9' : 'original';

      try {
        if (type === 'video') {
          const preview = await prepareVideoPreview(file);
          previewImage.src = preview.url;
          naturalWidth = preview.dimensions.width;
          naturalHeight = preview.dimensions.height;
          originalSizeLabel.textContent = `${naturalWidth} × ${naturalHeight}`;
          currentType = 'video';
          bindCropper(ratioMap[ratioSelect.value]);
          modal.show();
        } else {
          const reader = new FileReader();
          reader.onload = function () {
            previewImage.src = reader.result;
            const temp = new Image();
            temp.onload = () => {
              naturalWidth = temp.naturalWidth;
              naturalHeight = temp.naturalHeight;
              originalSizeLabel.textContent = `${naturalWidth} × ${naturalHeight}`;
              bindCropper(ratioMap[ratioSelect.value]);
              modal.show();
            };
            temp.src = reader.result;
          };
          reader.readAsDataURL(file);
        }
      } catch (error) {
        errorBox.textContent = error.message || 'Unable to prepare media for editing.';
        errorBox.classList.remove('d-none');
      }

      if (urlField) {
        urlField.value = '';
      }

      if (adjustButton) {
        adjustButton.classList.remove('d-none');
      }
    });

    if (adjustButton) {
      adjustButton.addEventListener('click', () => {
        if (!fileInput.files.length) {
          fileInput.click();
          return;
        }
        currentInput = fileInput;
        const file = fileInput.files[0];
        const type = (mediaTypeSelect?.value === 'video' || file.type.startsWith('video')) ? 'video' : 'image';
        currentFile = file;
        currentType = type;
        resetModalState();
        ratioSelect.value = type === 'video' ? '16:9' : 'original';

        if (type === 'video') {
          prepareVideoPreview(file)
            .then((preview) => {
              previewImage.src = preview.url;
              naturalWidth = preview.dimensions.width;
              naturalHeight = preview.dimensions.height;
              originalSizeLabel.textContent = `${naturalWidth} × ${naturalHeight}`;
              currentType = 'video';
              bindCropper(ratioMap[ratioSelect.value]);
              modal.show();
            })
            .catch((error) => {
              errorBox.textContent = error.message || 'Unable to prepare video for editing.';
              errorBox.classList.remove('d-none');
            });
        } else {
          const reader = new FileReader();
          reader.onload = function () {
            previewImage.src = reader.result;
            const temp = new Image();
            temp.onload = () => {
              naturalWidth = temp.naturalWidth;
              naturalHeight = temp.naturalHeight;
              originalSizeLabel.textContent = `${naturalWidth} × ${naturalHeight}`;
              currentType = 'image';
              bindCropper(ratioMap[ratioSelect.value]);
              modal.show();
            };
            temp.src = reader.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }
  }

  forms.forEach(attachHandlers);
})();
