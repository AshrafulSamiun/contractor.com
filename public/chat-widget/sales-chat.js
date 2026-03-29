function initSalesChatWidget() {
  var salesChat = document.querySelector('.sales-chat-widget');
  if (!salesChat) {
    return;
  }

  var chatToggle = salesChat.querySelector('.sales-chat-toggle');
  var chatPanel = salesChat.querySelector('.sales-chat-panel');
  var chatClose = salesChat.querySelector('.sales-chat-close');
  var chatMinimize = salesChat.querySelector('.sales-chat-minimize');
  var chatCancel = salesChat.querySelector('.sales-chat-btn-secondary');
  var chatForm = salesChat.querySelector('.sales-chat-form');
  var chatIntro = salesChat.querySelector('.sales-chat-intro');
  var chatThread = salesChat.querySelector('.sales-chat-thread');
  var chatInputForm = salesChat.querySelector('.sales-chat-input');
  var chatInputField = salesChat.querySelector('.sales-chat-input input');
  var chatSendButton = salesChat.querySelector('.sales-chat-send');
  var chatBody = salesChat.querySelector('.sales-chat-body');
  var chatNoField = salesChat.querySelector('[name="chat_no"]');
  var chatDateField = salesChat.querySelector('[name="chat_datetime"]');
  var callTimeField = salesChat.querySelector('[name="call_time"]');
  var countryField = salesChat.querySelector('[name="country_id"]');
  var chatEndpoint = salesChat.getAttribute('data-chat-endpoint');
  var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
  var personalDomains = [
    'gmail.com',
    'googlemail.com',
    'yahoo.com',
    'yahoo.co.uk',
    'yahoo.in',
    'yahoo.com.bd',
    'outlook.com',
    'hotmail.com',
    'live.com',
    'msn.com',
    'aol.com',
    'icloud.com',
    'me.com',
    'mac.com',
    'protonmail.com',
    'pm.me',
    'gmx.com',
    'mail.com',
    'yandex.com',
    'yandex.ru'
  ];

  function padNumber(value) {
    return value < 10 ? '0' + value : String(value);
  }

  function formatDateTime(date) {
    return date.getFullYear() + '-' + padNumber(date.getMonth() + 1) + '-' + padNumber(date.getDate()) + ' ' + padNumber(date.getHours()) + ':' + padNumber(date.getMinutes());
  }

  function formatDateTimeLocal(date) {
    return date.getFullYear() + '-' + padNumber(date.getMonth() + 1) + '-' + padNumber(date.getDate()) + 'T' + padNumber(date.getHours()) + ':' + padNumber(date.getMinutes());
  }

  function generateChatNo(date) {
    var stamp = date.getFullYear() + '' + padNumber(date.getMonth() + 1) + '' + padNumber(date.getDate());
    var random = Math.floor(1000 + Math.random() * 9000);
    return 'CHAT-' + stamp + '-' + random;
  }

  var chatSessionId = null;
  var chatProfile = null;

  function setChatMeta() {
    var now = new Date();
    if (!chatSessionId) {
      chatSessionId = generateChatNo(now);
    }
    if (chatNoField) {
      chatNoField.value = chatSessionId;
    }
    if (chatDateField) {
      chatDateField.value = formatDateTime(now);
    }
    if (callTimeField && !callTimeField.value) {
      callTimeField.value = formatDateTimeLocal(now);
    }
  }

  function hydrateCountryOptions() {
    if (!countryField || countryField.getAttribute('data-loaded') === '1') {
      return;
    }

    fetch('/api/v1/countries', {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
      .then(function(response) {
        return response.json().then(function(data) {
          return { ok: response.ok, data: data };
        }).catch(function() {
          return { ok: response.ok, data: {} };
        });
      })
      .then(function(result) {
        if (!result.ok || !result.data || !result.data.success || !Array.isArray(result.data.data)) {
          return;
        }

        var list = result.data.data;
        var currentValue = countryField.value;
        while (countryField.options.length > 1) {
          countryField.remove(1);
        }

        list.forEach(function(country) {
          if (!country || !country.id || !country.country_name) {
            return;
          }
          var option = document.createElement('option');
          option.value = String(country.id);
          option.textContent = country.country_name;
          countryField.appendChild(option);
        });

        if (currentValue) {
          countryField.value = currentValue;
        }
        countryField.setAttribute('data-loaded', '1');
      })
      .catch(function() {
        // Keep widget usable even if countries endpoint is unavailable.
      });
  }

  function applyDefaultCallTime(value) {
    if (!value) {
      return '';
    }
    var now = new Date();
    var defaultTime = padNumber(now.getHours()) + ':' + padNumber(now.getMinutes());
    if (value.indexOf('T') === -1) {
      return value + 'T' + defaultTime;
    }
    var parts = value.split('T');
    if (!parts[1] || parts[1] === '00:00') {
      return parts[0] + 'T' + defaultTime;
    }
    return value;
  }

  function setChatOpen(isOpen) {
    if (!chatPanel || !chatToggle) {
      return;
    }
    chatPanel.classList.toggle('is-open', isOpen);
    chatPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    chatToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    if (isOpen) {
      setChatMeta();
      hydrateCountryOptions();
    }
    if (isOpen && chatInputField && !chatInputForm.classList.contains('sales-chat-hidden')) {
      chatInputField.focus();
    }
  }

  function setFieldError(fieldName, message) {
    var field = chatForm.querySelector('[name="' + fieldName + '"]');
    var error = chatForm.querySelector('[data-error-for="' + fieldName + '"]');
    if (field) {
      if (message) {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
      } else {
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
      }
    }
    if (error) {
      error.textContent = message || '';
    }
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function normalizePhoneValue(value) {
    var raw = String(value || '').trim();
    if (!raw) {
      return '';
    }

    var normalized = raw.replace(/[^\d+]/g, '');
    if (!normalized) {
      return '';
    }

    if (normalized.indexOf('00') === 0) {
      normalized = '+' + normalized.slice(2);
    }

    if (normalized.indexOf('+') === 0) {
      normalized = '+' + normalized.slice(1).replace(/\D/g, '');
    } else {
      normalized = '+' + normalized.replace(/\D/g, '');
    }

    return normalized === '+' ? '' : normalized;
  }

  function isValidE164Phone(phone) {
    return /^\+[1-9]\d{7,14}$/.test(String(phone || '').trim());
  }

  function validateChatForm() {
    var isValid = true;
    var firstName = chatForm.querySelector('[name="first_name"]');
    var lastName = chatForm.querySelector('[name="last_name"]');
    var companyName = chatForm.querySelector('[name="company_name"]');
    var workEmail = chatForm.querySelector('[name="work_email"]');
    var phone = chatForm.querySelector('[name="business_phone"]');
    var country = chatForm.querySelector('[name="country_id"]');
    var city = chatForm.querySelector('[name="city"]');
    var callTime = chatForm.querySelector('[name="call_time"]');
    var inquiry = chatForm.querySelector('[name="inquiry"]');

    if (!firstName || !firstName.value.trim()) {
      setFieldError('first_name', 'First name is required.');
      isValid = false;
    } else {
      setFieldError('first_name', '');
    }

    if (!lastName || !lastName.value.trim()) {
      setFieldError('last_name', 'Last name is required.');
      isValid = false;
    } else {
      setFieldError('last_name', '');
    }

    if (!companyName || !companyName.value.trim()) {
      setFieldError('company_name', 'Company name is required.');
      isValid = false;
    } else {
      setFieldError('company_name', '');
    }

    var emailValue = workEmail ? workEmail.value.trim() : '';
    if (!emailValue) {
      setFieldError('work_email', 'Business email is required.');
      isValid = false;
    } else if (!isValidEmail(emailValue)) {
      setFieldError('work_email', 'Please enter a valid business email to continue.');
      isValid = false;
    } else {
      var domain = emailValue.split('@').pop().toLowerCase();
      if (personalDomains.indexOf(domain) !== -1) {
        setFieldError('work_email', 'Please use a business email address (no gmail, yahoo, outlook, or hotmail).');
        isValid = false;
      } else {
        setFieldError('work_email', '');
      }
    }

    var normalizedPhone = phone ? normalizePhoneValue(phone.value) : '';
    if (phone) {
      phone.value = normalizedPhone;
    }

    if (!phone || !normalizedPhone) {
      setFieldError('business_phone', 'Business phone is required.');
      isValid = false;
    } else if (!isValidE164Phone(normalizedPhone)) {
      setFieldError('business_phone', 'Phone number must be in E.164 format (e.g. +14165550100).');
      isValid = false;
    } else {
      setFieldError('business_phone', '');
    }

    if (!country || !country.value.trim()) {
      setFieldError('country_id', 'Please select a country.');
      isValid = false;
    } else {
      setFieldError('country_id', '');
    }

    if (!city || !city.value.trim()) {
      setFieldError('city', 'City is required.');
      isValid = false;
    } else {
      setFieldError('city', '');
    }

    if (!callTime || !callTime.value.trim()) {
      setFieldError('call_time', 'Please choose a preferred call time.');
      isValid = false;
    } else {
      setFieldError('call_time', '');
    }

    if (!inquiry || !inquiry.value.trim()) {
      setFieldError('inquiry', 'Please tell us what you want to know.');
      isValid = false;
    } else {
      setFieldError('inquiry', '');
    }

    return isValid;
  }

  function buildChatProfile() {
    var getValue = function(name) {
      var field = chatForm.querySelector('[name="' + name + '"]');
      return field ? field.value.trim() : '';
    };
    var selectedCountryName = '';
    if (countryField && countryField.selectedIndex >= 0) {
      var selected = countryField.options[countryField.selectedIndex];
      if (selected && selected.value) {
        selectedCountryName = selected.textContent.trim();
      }
    }
    return {
      chat_no: chatNoField ? chatNoField.value : '',
      chat_datetime: chatDateField ? chatDateField.value : '',
      first_name: getValue('first_name'),
      last_name: getValue('last_name'),
      company_name: getValue('company_name'),
      work_email: getValue('work_email'),
      business_phone: normalizePhoneValue(getValue('business_phone')),
      country_id: getValue('country_id'),
      country: selectedCountryName,
      city: getValue('city'),
      call_time: getValue('call_time'),
      inquiry: getValue('inquiry'),
    };
  }

  function appendChatMessage(role, text) {
    if (!chatThread) {
      return;
    }
    var message = document.createElement('div');
    message.className = 'sales-chat-message ' + role;
    var bubble = document.createElement('div');
    bubble.className = 'sales-chat-bubble';
    bubble.textContent = text;
    message.appendChild(bubble);
    chatThread.appendChild(message);
    if (chatBody) {
      chatBody.scrollTop = chatBody.scrollHeight;
    } else {
      chatThread.scrollTop = chatThread.scrollHeight;
    }
    if (role === 'assistant') {
      highlightSendButton();
    }
  }

  var typingIndicatorElement = null;

  function showTypingIndicator() {
    if (!chatThread || typingIndicatorElement) {
      return;
    }
    typingIndicatorElement = document.createElement('div');
    typingIndicatorElement.className = 'sales-chat-message assistant sales-chat-typing';
    var bubble = document.createElement('div');
    bubble.className = 'sales-chat-bubble';
    bubble.textContent = 'Agent is typing...';
    typingIndicatorElement.appendChild(bubble);
    chatThread.appendChild(typingIndicatorElement);
    if (chatBody) {
      chatBody.scrollTop = chatBody.scrollHeight;
    }
  }

  function removeTypingIndicator() {
    if (typingIndicatorElement && typingIndicatorElement.parentNode) {
      typingIndicatorElement.parentNode.removeChild(typingIndicatorElement);
    }
    typingIndicatorElement = null;
  }

  function updateSendButtonState() {
    if (!chatSendButton || !chatInputField) {
      return;
    }
    var shouldEnable = chatInputField.value.trim().length > 0;
    chatSendButton.disabled = !shouldEnable;
    chatSendButton.classList.toggle('is-disabled', !shouldEnable);
  }

  function highlightSendButton() {
    if (!chatSendButton) {
      return;
    }
    chatSendButton.classList.add('sales-chat-send-highlight');
    window.setTimeout(function() {
      chatSendButton.classList.remove('sales-chat-send-highlight');
    }, 600);
  }

  var chatLanguage = 'en';

  function wantsBangla(message) {
    var text = message || '';
    return /\bbangla\b/i.test(text) || text.indexOf('\u09ac\u09be\u0982\u09b2\u09be') !== -1;
  }

  function buildEnglishReply(message) {
    if (message.indexOf('price') !== -1 || message.indexOf('pricing') !== -1 || message.indexOf('plan') !== -1 || message.indexOf('cost') !== -1) {
      return "I can help with pricing and plans.\n- Company size\n- Number of buildings/units\n- Required features\n\nShare these and I will guide you.";
    }
    if (message.indexOf('integration') !== -1 || message.indexOf('api') !== -1 || message.indexOf('webhook') !== -1) {
      return "I can help with integrations.\n- Which system do you want to connect?\n- Do you need API or webhook support?\n\nShare the flow and I will guide you step by step.";
    }
    if (message.indexOf('onboarding') !== -1 || message.indexOf('setup') !== -1 || message.indexOf('start') !== -1 || message.indexOf('getting started') !== -1) {
      return "I can help with onboarding and setup.\n- Team roles (admin/manager/staff)\n- Data import needs\n\nShare these and I will suggest the right steps.";
    }
    if (message.indexOf('security') !== -1 || message.indexOf('secure') !== -1 || message.indexOf('compliance') !== -1) {
      return "I can help with security and compliance.\n- Required standards (SOC/ISO)\n- Access control needs\n\nIf you have specific questions, I can answer or point to the documentation.";
    }
    return "Thanks for reaching out. I can help with:\n- Parcel operations and notifications\n- Pricing and plans\n- Integrations and security\n\nShare your use case and I will guide you.";
  }

  function buildBanglaReply(message) {
    return "আপনার প্রশ্নটি বিস্তারিত বললে আমি দ্রুত সাহায্য করতে পারব। Pricing/Onboarding/Integration—যে বিষয়ে জানতে চান, বলুন।";
  }

  function buildAssistantReply(userMessage) {
    var message = (userMessage || '').toLowerCase();
    if (wantsBangla(message)) {
      chatLanguage = 'bn';
    }
    if (chatLanguage === 'bn') {
      return buildBanglaReply(message);
    }
    return buildEnglishReply(message);
  }

  function sendChatMessage(message) {
    if (!chatEndpoint) {
      appendChatMessage('assistant', buildAssistantReply(message));
      return;
    }

    showTypingIndicator();
    var payload = chatProfile || {};
    payload.message = message;

    var headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken;
    }

    fetch(chatEndpoint, {
      method: 'POST',
      headers: headers,
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    })
      .then(function(response) {
        return response.json().then(function(data) {
          return { ok: response.ok, status: response.status, data: data };
        }).catch(function() {
          return { ok: response.ok, status: response.status, data: {} };
        });
      })
      .then(function(result) {
        if (result.ok && result.data && result.data.reply) {
          removeTypingIndicator();
          appendChatMessage('assistant', result.data.reply);
          return;
        }
        var errorMessage = 'Thanks for your message. A specialist will follow up within 1 business day.';
        if (result.data && result.status === 422 && result.data.errors) {
          var fields = Object.keys(result.data.errors);
          if (fields.length && result.data.errors[fields[0]].length) {
            errorMessage = result.data.errors[fields[0]][0];
          }
        } else if (result.data && result.data.error && result.status && result.status < 500) {
          errorMessage = result.data.error;
        }
        removeTypingIndicator();
        appendChatMessage('assistant', errorMessage);
      })
      .catch(function() {
        removeTypingIndicator();
        appendChatMessage('assistant', 'Thanks for your message. A specialist will follow up within 1 business day.');
      });
  }

  if (chatToggle && chatPanel) {
    chatToggle.addEventListener('click', function() {
      setChatOpen(!chatPanel.classList.contains('is-open'));
    });
  }

  if (chatClose) {
    chatClose.addEventListener('click', function() {
      setChatOpen(false);
    });
  }

  if (chatMinimize) {
    chatMinimize.addEventListener('click', function() {
      setChatOpen(false);
    });
  }

  if (chatCancel) {
    chatCancel.addEventListener('click', function() {
      setChatOpen(false);
    });
  }

  if (chatForm) {
    chatForm.addEventListener('submit', function(event) {
      event.preventDefault();
      if (!validateChatForm()) {
        return;
      }
      chatProfile = buildChatProfile();
      chatForm.classList.add('sales-chat-hidden');
      if (chatIntro) {
        chatIntro.classList.add('sales-chat-hidden');
      }
      if (chatThread) {
        chatThread.classList.remove('sales-chat-hidden');
      }
      if (chatInputForm) {
        chatInputForm.classList.remove('sales-chat-hidden');
      }
      if (chatInputField) {
        chatInputField.focus();
      }
    });
  }

  if (callTimeField) {
    callTimeField.addEventListener('change', function() {
      var normalized = applyDefaultCallTime(callTimeField.value);
      if (normalized && normalized !== callTimeField.value) {
        callTimeField.value = normalized;
      }
    });
  }

  if (chatInputForm) {
    chatInputForm.addEventListener('submit', function(event) {
      event.preventDefault();
      if (!chatInputField) {
        return;
      }
      var message = chatInputField.value.trim();
      if (!message) {
        return;
      }
      appendChatMessage('user', message);
      chatInputField.value = '';
      updateSendButtonState();
      window.setTimeout(function() {
        sendChatMessage(message);
      }, 150);
    });
  }

  if (chatInputField) {
    chatInputField.addEventListener('input', updateSendButtonState);
  }

  hydrateCountryOptions();
  updateSendButtonState();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSalesChatWidget);
} else {
  initSalesChatWidget();
}
