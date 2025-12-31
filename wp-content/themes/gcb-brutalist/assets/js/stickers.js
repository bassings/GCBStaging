/**
 * GCB Social Sticker Drag-and-Drop
 * Vanilla ES6+ - No jQuery
 *
 * Features:
 * - Mouse & touch drag support
 * - GPU-accelerated transforms (translate3d)
 * - localStorage persistence per post
 * - Prevents page scroll during touch drag
 * - Brutalist instant state changes (no smooth transitions)
 *
 * @package GCB_Brutalist
 */

class GCBStickerDragger {
	constructor(stickerElement) {
		this.sticker = stickerElement;
		this.postId = this.sticker.dataset.postId;
		this.stickerIndex = this.sticker.dataset.stickerIndex;
		this.storageKey = `gcb-sticker-${this.postId}-${this.stickerIndex}`;

		// Drag state
		this.isDragging = false;
		this.startX = 0;
		this.startY = 0;
		this.currentX = 0;
		this.currentY = 0;

		// Bind methods to preserve context
		this.onMouseDown = this.handleMouseDown.bind(this);
		this.onMouseMove = this.handleMouseMove.bind(this);
		this.onMouseUp = this.handleMouseUp.bind(this);
		this.onTouchStart = this.handleTouchStart.bind(this);
		this.onTouchMove = this.handleTouchMove.bind(this);
		this.onTouchEnd = this.handleTouchEnd.bind(this);

		this.init();
	}

	init() {
		// Restore saved position from localStorage
		this.restorePosition();

		// Register event listeners
		this.sticker.addEventListener('mousedown', this.onMouseDown);
		this.sticker.addEventListener('touchstart', this.onTouchStart, { passive: false });
	}

	restorePosition() {
		try {
			const savedPosition = localStorage.getItem(this.storageKey);
			if (savedPosition) {
				const { x, y } = JSON.parse(savedPosition);
				this.currentX = x;
				this.currentY = y;
				this.applyTransform();
			}
		} catch (error) {
			console.warn('Failed to restore sticker position:', error);
		}
	}

	savePosition() {
		try {
			const position = JSON.stringify({
				x: this.currentX,
				y: this.currentY
			});
			localStorage.setItem(this.storageKey, position);
		} catch (error) {
			// localStorage quota exceeded or disabled
			console.warn('Failed to save sticker position:', error);
		}
	}

	handleMouseDown(e) {
		this.startDrag(e.clientX, e.clientY);

		// Attach global listeners for drag
		document.addEventListener('mousemove', this.onMouseMove);
		document.addEventListener('mouseup', this.onMouseUp);

		e.preventDefault(); // Prevent text selection
	}

	handleMouseMove(e) {
		if (!this.isDragging) return;

		const deltaX = e.clientX - this.startX;
		const deltaY = e.clientY - this.startY;

		this.currentX += deltaX;
		this.currentY += deltaY;

		this.startX = e.clientX;
		this.startY = e.clientY;

		this.applyTransform();
	}

	handleMouseUp() {
		this.endDrag();

		document.removeEventListener('mousemove', this.onMouseMove);
		document.removeEventListener('mouseup', this.onMouseUp);
	}

	handleTouchStart(e) {
		const touch = e.touches[0];
		this.startDrag(touch.clientX, touch.clientY);

		document.addEventListener('touchmove', this.onTouchMove, { passive: false });
		document.addEventListener('touchend', this.onTouchEnd);

		e.preventDefault(); // CRITICAL: Prevent page scroll
	}

	handleTouchMove(e) {
		if (!this.isDragging) return;

		const touch = e.touches[0];
		const deltaX = touch.clientX - this.startX;
		const deltaY = touch.clientY - this.startY;

		this.currentX += deltaX;
		this.currentY += deltaY;

		this.startX = touch.clientX;
		this.startY = touch.clientY;

		this.applyTransform();

		e.preventDefault(); // CRITICAL: Prevent page scroll during drag
	}

	handleTouchEnd() {
		this.endDrag();

		document.removeEventListener('touchmove', this.onTouchMove);
		document.removeEventListener('touchend', this.onTouchEnd);
	}

	startDrag(clientX, clientY) {
		this.isDragging = true;
		this.startX = clientX;
		this.startY = clientY;

		// Add grabbing cursor class
		this.sticker.classList.add('is-grabbing');
	}

	endDrag() {
		this.isDragging = false;

		// Remove grabbing cursor class
		this.sticker.classList.remove('is-grabbing');

		// Save final position to localStorage
		this.savePosition();
	}

	applyTransform() {
		// Use translate3d for GPU acceleration
		// Brutalist = instant transform, no transitions
		this.sticker.style.transform = `translate3d(${this.currentX}px, ${this.currentY}px, 0)`;
	}

	destroy() {
		// Cleanup: Remove all event listeners
		this.sticker.removeEventListener('mousedown', this.onMouseDown);
		this.sticker.removeEventListener('touchstart', this.onTouchStart);
		document.removeEventListener('mousemove', this.onMouseMove);
		document.removeEventListener('mouseup', this.onMouseUp);
		document.removeEventListener('touchmove', this.onTouchMove);
		document.removeEventListener('touchend', this.onTouchEnd);
	}
}

// Initialize all stickers when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
	const stickers = document.querySelectorAll('.gcb-sticker');

	if (stickers.length === 0) {
		return; // No stickers on page
	}

	// Instantiate dragger for each sticker
	stickers.forEach(stickerElement => {
		new GCBStickerDragger(stickerElement);
	});

	console.log(`✅ ${stickers.length} stickers initialized with drag-and-drop`);
});
