/**
 * Tutorial Manager
 * Handles all tutorial initialization and management
 */

import { initRegistrationTutorial, initFamilyRegistrationTutorial } from './registration-guide.js';
import { initDashboardTutorial, initQuickTips } from './dashboard-guide.js';

export class TutorialManager {
    constructor() {
        this.tutorials = {
            registration: null,
            family: null,
            dashboard: null,
            tips: null
        };
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupTutorials());
        } else {
            this.setupTutorials();
        }
    }

    setupTutorials() {
        // Add tutorial buttons to the page
        this.addTutorialButtons();

        // Initialize tutorials (lazy load)
        this.tutorials.registration = initRegistrationTutorial;
        this.tutorials.family = initFamilyRegistrationTutorial;
        this.tutorials.dashboard = initDashboardTutorial;
        this.tutorials.tips = initQuickTips;
    }

    addTutorialButtons() {
        // Add tutorial button to welcome page
        const welcomePage = document.getElementById('forms-section');
        if (welcomePage) {
            const tutorialBtnContainer = document.createElement('div');
            tutorialBtnContainer.className = 'mt-4 flex justify-center gap-2';
            tutorialBtnContainer.innerHTML = `
                <button id="startRegistrationTutorial" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium flex items-center gap-2">
                    <span>?</span>
                    <span>دليل التسجيل</span>
                </button>
                <button id="startFamilyTutorial" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium flex items-center gap-2">
                    <span>?</span>
                    <span>دليل تسجيل العائلة</span>
                </button>
            `;
            welcomePage.appendChild(tutorialBtnContainer);

            // Add event listeners
            document.getElementById('startRegistrationTutorial')?.addEventListener('click', () => {
                this.startTutorial('registration');
            });

            document.getElementById('startFamilyTutorial')?.addEventListener('click', () => {
                this.startTutorial('family');
            });
        }

        // Add tutorial button to dashboard
        const dashboardHeader = document.querySelector('header');
        if (dashboardHeader && dashboardHeader.querySelector('[data-tour="caller-list"]')) {
            const tutorialBtnDashboard = document.createElement('div');
            tutorialBtnDashboard.className = 'inline-flex gap-2 ml-4';
            tutorialBtnDashboard.innerHTML = `
                <button id="startDashboardTutorial" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm font-medium" title="شرح لوحة التحكم">
                    <span>? شرح</span>
                </button>
                <button id="startQuickTips" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition text-sm font-medium" title="نصائح سريعة">
                    <span>💡 نصائح</span>
                </button>
            `;
            dashboardHeader.appendChild(tutorialBtnDashboard);

            // Add event listeners
            document.getElementById('startDashboardTutorial')?.addEventListener('click', () => {
                this.startTutorial('dashboard');
            });

            document.getElementById('startQuickTips')?.addEventListener('click', () => {
                this.startTutorial('tips');
            });
        }
    }

    startTutorial(tutorialName) {
        const tutorial = this.tutorials[tutorialName];
        if (typeof tutorial === 'function') {
            const driverInstance = tutorial();
            driverInstance.drive();
        } else {
            console.warn(`Tutorial "${tutorialName}" not found`);
        }
    }

    // Check if user is new and show tutorial automatically
    showWelcomeTutorialIfNew() {
        const hasSeenTutorial = localStorage.getItem('tutorial_registration_shown');
        if (!hasSeenTutorial && this.tutorials.registration) {
            setTimeout(() => {
                const driverInstance = this.tutorials.registration();
                driverInstance.drive();
                localStorage.setItem('tutorial_registration_shown', 'true');
            }, 1000);
        }
    }

    resetTutorials() {
        localStorage.removeItem('tutorial_registration_shown');
        localStorage.removeItem('tutorial_dashboard_shown');
    }
}

// Initialize on page load
export const tutorialManager = new TutorialManager();
