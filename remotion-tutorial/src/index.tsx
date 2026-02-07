/**
 * AlSarya TV Remotion Tutorial - Main Entry Point
 * 
 * This file registers the root component for the Remotion video application.
 * The application showcases the AlSarya TV platform with bilingual support (Arabic/English).
 * 
 * Key Features:
 * - Dashboard overview with real-time statistics
 * - Frontend journey from splash to success screens
 * - Registration flow with validation
 * - Maintenance mode with countdown
 * - Winner selection system
 * 
 * Screenshots can be captured for each scene by rendering individual compositions.
 */

import {registerRoot} from '@remotion/core';
import {Root} from './Root';

// Register the root component to enable video rendering
registerRoot(Root);
