(function () {
  const cleanHtml = (html) => {
    if (!html) {
      return '';
    }
    let output = html.replace(/\u200B/g, '');
    output = output.replace(/<div><br\s*\/?><\/div>/gi, '<br>');
    output = output.replace(/^(<br\s*\/?>(\s|&nbsp;)*)+$/i, '');
    output = output.trim();
    if (output === '<br>' || output === '&nbsp;') {
      return '';
    }
    return output;
  };

  const syncEditor = (instance) => {
    const { textarea, content } = instance;
    if (!textarea || !content) {
      return;
    }
    const html = cleanHtml(content.innerHTML);
    textarea.value = html;
    const text = content.textContent.replace(/\u00a0/g, ' ').trim();
    content.dataset.empty = text.length === 0 && !html ? 'true' : 'false';
  };

  const exec = (command, value = null) => {
    try {
      document.execCommand(command, false, value);
    } catch (error) {
      // Ignore unsupported commands.
    }
  };

  const updateActiveStates = (instance) => {
    if (!instance.toolbar) {
      return;
    }

    instance.toolbar.querySelectorAll('.rich-btn[data-command]').forEach((button) => {
      const command = button.dataset.command;
      let isActive = false;
      try {
        isActive = document.queryCommandState(command);
      } catch (error) {
        isActive = false;
      }
      if (isActive) {
        button.classList.add('is-active');
      } else {
        button.classList.remove('is-active');
      }
    });

    if (instance.blockSelect) {
      let blockValue = 'p';
      try {
        const commandValue = document.queryCommandValue('formatBlock');
        if (typeof commandValue === 'string' && commandValue.length) {
          const normalized = commandValue.replace(/[<>]/g, '').toLowerCase();
          if (['h2', 'h3', 'blockquote'].includes(normalized)) {
            blockValue = normalized;
          }
        }
      } catch (error) {
        blockValue = 'p';
      }
      instance.blockSelect.value = blockValue;
    }
  };

  const createButton = (config, instance) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'rich-btn';
    button.dataset.command = config.command;
    button.innerHTML = config.icon;
    button.title = config.label;

    button.addEventListener('mousedown', (event) => {
      event.preventDefault();
    });

    button.addEventListener('click', (event) => {
      event.preventDefault();
      if (!instance.content) {
        return;
      }
      instance.content.focus();

      if (config.prompt) {
        const answer = window.prompt(config.prompt, config.defaultValue || '');
        if (!answer) {
          if (config.command === 'createLink') {
            exec('unlink');
          }
          return;
        }
        exec(config.command, answer);
      } else if (config.command === 'removeFormat') {
        exec('removeFormat');
        exec('unlink');
      } else if (config.value) {
        exec(config.command, config.value);
      } else {
        exec(config.command);
      }

      syncEditor(instance);
      updateActiveStates(instance);
    });

    return button;
  };

  const initEditor = (textarea) => {
    const instance = { textarea, toolbar: null, content: null, blockSelect: null };

    const wrapper = document.createElement('div');
    wrapper.className = 'rich-editor js-inline-editor';

    const toolbar = document.createElement('div');
    toolbar.className = 'rich-toolbar';
    instance.toolbar = toolbar;

    const blockSelect = document.createElement('select');
    blockSelect.className = 'rich-block';
    blockSelect.innerHTML = [
      '<option value="p">Paragraph</option>',
      '<option value="h2">Heading 2</option>',
      '<option value="h3">Heading 3</option>',
      '<option value="blockquote">Quote</option>'
    ].join('');
    blockSelect.addEventListener('change', () => {
      if (!instance.content) {
        return;
      }
      instance.content.focus();
      const value = blockSelect.value;
      const tag = value === 'p' ? 'p' : value;
      exec('formatBlock', `<${tag}>`);
      updateActiveStates(instance);
      syncEditor(instance);
    });
    toolbar.appendChild(blockSelect);
    instance.blockSelect = blockSelect;

    const commands = [
      { command: 'bold', icon: '<i class="bi bi-type-bold"></i>', label: 'Bold' },
      { command: 'italic', icon: '<i class="bi bi-type-italic"></i>', label: 'Italic' },
      { command: 'underline', icon: '<i class="bi bi-type-underline"></i>', label: 'Underline' },
      { command: 'insertUnorderedList', icon: '<i class="bi bi-list-ul"></i>', label: 'Bulleted list' },
      { command: 'insertOrderedList', icon: '<i class="bi bi-list-ol"></i>', label: 'Numbered list' },
      { command: 'createLink', icon: '<i class="bi bi-link-45deg"></i>', label: 'Insert link', prompt: 'Enter URL', defaultValue: 'https://example.com' },
      { command: 'unlink', icon: '<i class="bi bi-link-slash"></i>', label: 'Remove link' },
      { command: 'removeFormat', icon: '<i class="bi bi-eraser"></i>', label: 'Clear formatting' }
    ];

    commands.forEach((config) => {
      toolbar.appendChild(createButton(config, instance));
    });

    const content = document.createElement('div');
    content.className = 'rich-content';
    content.contentEditable = 'true';
    content.dataset.placeholder = textarea.getAttribute('placeholder') || 'Start writing...';
    content.innerHTML = textarea.value.trim();
    instance.content = content;

    wrapper.appendChild(toolbar);
    wrapper.appendChild(content);

    textarea.classList.add('js-rich-source-hidden');
    textarea.parentNode.insertBefore(wrapper, textarea);

    const handleInput = () => {
      syncEditor(instance);
    };

    content.addEventListener('input', handleInput);
    content.addEventListener('blur', handleInput);
    content.addEventListener('focus', () => updateActiveStates(instance));
    content.addEventListener('keyup', () => updateActiveStates(instance));
    content.addEventListener('mouseup', () => updateActiveStates(instance));
    content.addEventListener('paste', (event) => {
      event.preventDefault();
      const text = event.clipboardData ? event.clipboardData.getData('text/plain') : '';
      exec('insertText', text);
      syncEditor(instance);
    });

    try {
      exec('defaultParagraphSeparator', 'p');
    } catch (error) {
      // Older browsers may not support this command.
    }

    syncEditor(instance);

    return instance;
  };

  document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('textarea.js-rich-editor');
    if (!textareas.length) {
      return;
    }

    const editors = Array.from(textareas).map((textarea) => initEditor(textarea));

    const syncAll = () => {
      editors.forEach((instance) => syncEditor(instance));
    };

    document.querySelectorAll('form').forEach((form) => {
      form.addEventListener('submit', syncAll);
    });

    document.addEventListener('selectionchange', () => {
      const activeElement = document.activeElement;
      const activeInstance = editors.find((instance) => instance.content === activeElement);
      if (activeInstance) {
        updateActiveStates(activeInstance);
      }
    });
  });
})();
