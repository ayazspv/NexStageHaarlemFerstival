import { ref } from 'vue';

export function useBandDetails() {
  const selectedBand = ref(null);
  
  // Animation helpers for modal
  const openModalWithAnimation = () => {
    document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
  };

  const closeModalWithAnimation = () => {
    document.body.style.overflow = ''; // Restore scrolling
  };

  // Updated band details functions
  const showBandDetails = (band) => {
    selectedBand.value = band;
    openModalWithAnimation();
  };

  const closeBandDetails = () => {
    closeModalWithAnimation();
    setTimeout(() => {
      selectedBand.value = null;
    }, 300); // animation duration
  };

  return {
    selectedBand,
    showBandDetails,
    closeBandDetails
  };
}