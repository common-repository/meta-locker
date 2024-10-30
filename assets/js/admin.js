async function handleClick(e) {
  e.preventDefault();
  setElementTextAndDisable(e.currentTarget, "ACTIVATING...");
  await activate_plugin();
}

function setElementTextAndDisable(element, text) {
  element.textContent = text;
  element.setAttribute("disabled", true);
}

async function activate_plugin() {
  const noticeEl = document.getElementById("messager");
  const defaultMsg = noticeEl.textContent;
  const activateBtn = document.getElementById("meta-plugin-activate-btn");
  const tosBox = document.getElementById("accept_tos");

  if (!tosBox.checked) {
    displayErrorAndReset(noticeEl, defaultMsg, activateBtn, metaLocker.tosRequired);
    return;
  }

  const emailInput = document.querySelector("#registration_email");
  try {
    const newAccounts = await ethereum.request({ method: "eth_requestAccounts" });
    handleNewAccounts(newAccounts, emailInput, noticeEl, defaultMsg, activateBtn);
  } catch (error) {
    console.log(`Error requesting Ethereum accounts: ${error}`);
  }
}

async function displayErrorAndReset(noticeEl, defaultMsg, activateBtn, errorMessage) {
  updateUI(noticeEl, activateBtn, "err", errorMessage, "ACTIVATE");
  setTimeout(() => {
    noticeEl.textContent = defaultMsg;
    noticeEl.classList.remove("err");
  }, 3000);
}

async function handleNewAccounts(newAccounts, emailInput, noticeEl, defaultMsg, activateBtn) {
  if (newAccounts.length > 0) {
    const connectedAddress = newAccounts[0];
    try {
      const chainId = await ethereum.request({ method: "eth_chainId" });
      const currentChainId = parseInt(chainId, 16);
      const token = networkInfo.symbols[currentChainId] ?? 'Unknown';
      handleChainId(currentChainId, emailInput, connectedAddress, token, noticeEl, defaultMsg, activateBtn);
    } catch (error) {
      updateUI(noticeEl, activateBtn, "err", "Error getting the current Ethereum chainId. Please switch to mainnet.", "ACTIVATE");
      console.log("Error getting the current Ethereum chainId.");
    }
  } else {
    console.log("No Ethereum accounts found.");
  }
}

async function handleChainId(currentChainId, emailInput, connectedAddress, token, noticeEl, defaultMsg, activateBtn) {
  if (!networkInfo.testnets.includes(currentChainId)) {
    await activateOnMainnet(emailInput, connectedAddress, token, noticeEl, activateBtn);
  } else {
    handleTestnet(noticeEl, defaultMsg, activateBtn);
  }
}

async function activateOnMainnet(emailInput, connectedAddress, token, noticeEl, activateBtn) {
  try {
    const response = await fetch(ajaxurl, {
      method: "POST",
      body: new URLSearchParams({
        email: emailInput.value,
        address: connectedAddress,
        plugin: "meta-locker",
        ticker: token,
        action: "metalocker_activate_site",
      }),
    });
    const result = await response.json();
    if (result.success) {
      updateUI(noticeEl, activateBtn, "ok", result.message, "ACTIVATED");
      setTimeout(() => (window.location.href = metaLocker.adminURL), 3000);
    } else {
      updateUI(noticeEl, activateBtn, "err", result.message, "ACTIVATE");
    }
  } catch (err) {
    console.log(err);
  }
}

async function handleTestnet(noticeEl, defaultMsg, activateBtn) {
  updateUI(noticeEl, activateBtn, "err", "Please switch to mainnet.", "ACTIVATE");
  try {
    await ethereum.request({ method: "wallet_switchEthereumChain", params: [{ chainId: "0x1" }] });
    noticeEl.classList.remove("err");
    noticeEl.textContent = defaultMsg;
    activateBtn.textContent = "ACTIVATE";
    activate_plugin();
  } catch (error) {
    console.log(error);
  }
}

function updateUI(noticeEl, activateBtn, className, noticeText, btnText) {
  noticeEl.classList.add(className);
  noticeEl.textContent = noticeText;
  activateBtn.textContent = btnText;
  if (btnText === "ACTIVATE") {
    activateBtn.removeAttribute("disabled");
  }
}

window.addEventListener("DOMContentLoaded", () => {
  const activateBtn = document.getElementById("meta-plugin-activate-btn");
  if (activateBtn) {
    activateBtn.removeEventListener("click", handleClick);
    activateBtn.addEventListener("click", handleClick);
  }
});
