#!/usr/bin/env bash

################################################################################
# QUICK START - Deploy Registration Fix to Production
# AlSarya TV - February 19, 2026
################################################################################

cat << 'EOF'

╔════════════════════════════════════════════════════════════════════════════╗
║                                                                            ║
║     🚀 AlSarya TV Production Deployment - Registration Fix                ║
║                                                                            ║
║     Status: ✅ READY FOR DEPLOYMENT                                       ║
║     Risk Level: LOW                                                       ║
║     Estimated Time: 3-5 minutes                                           ║
║                                                                            ║
╚════════════════════════════════════════════════════════════════════════════╝

WHAT WAS FIXED:
═══════════════════════════════════════════════════════════════════════════

✅ Caller Model Boot Method
   - Allow public users to update: name, phone, ip_address, status
   - Block public users from updating: is_winner, is_selected, level, notes
   - Maintains security while enabling registration

✅ Enhanced Deployment Logging
   - Full logs: storage/logs/deployments/deploy_*.log
   - Performance tracking: deploy_performance.log
   - Better error context and debugging

✅ Comprehensive Test Suite
   - 12 PEST tests covering all registration scenarios
   - Security validation tests
   - Edge case coverage

DEPLOYMENT OPTIONS:
═══════════════════════════════════════════════════════════════════════════

OPTION 1 - Quick Deploy (Recommended)
────────────────────────────────────────────────────────────────────────────

  cd /Users/aldoyh/Sites/RAMADAN/alsaryatv
  ./publish.sh --force

  What happens:
  • Commits changes locally if needed
  • Pushes to GitHub (main branch)
  • Triggers remote deployment script
  • Logs everything to storage/logs/deployments/

OPTION 2 - Automated Script with Verification
────────────────────────────────────────────────────────────────────────────

  bash deploy_registration_fix.sh

  What happens:
  • Runs pre-deployment checks
  • Creates backup on production
  • Deploys the fix
  • Verifies registration works
  • Shows deployment logs

OPTION 3 - Manual SSH Deploy (Advanced)
────────────────────────────────────────────────────────────────────────────

  ssh root@alsarya.tv << 'EOF'
      cd /home/alsarya.tv/public_html
      git pull origin main
      php artisan optimize:clear
      echo "✅ Deployment complete"
  EOF


COMMIT STATUS:
═══════════════════════════════════════════════════════════════════════════

Files changed and ready to commit:

  ✓ app/Models/Caller.php
    → Fixed boot() method to allow public registration updates

  ✓ deploy.sh
    → Added comprehensive logging infrastructure

  ✓ tests/Feature/CallerRegistrationSecurityTest.php
    → Added 12 PEST tests for registration security

Documents created:

  ✓ IMPLEMENTATION_SUMMARY.md
    → Complete technical documentation

  ✓ REGISTRATION_FIX_DEPLOYMENT.md
    → Deployment guide with manual instructions

  ✓ deploy_registration_fix.sh
    → Automated deployment script with verification


TESTING THE FIX (After Deployment):
═══════════════════════════════════════════════════════════════════════════

1. Test Registration Form
   ─────────────────────────────────────────────────────────────────────
   Visit: https://alsarya.tv
   
   Fill in:
   - Name: Test User
   - CPR: 123456789
   - Phone: +97366123456
   
   Submit and verify:
   - No errors
   - Success page displays
   - Caller record created

2. Check Database
   ─────────────────────────────────────────────────────────────────────
   ssh root@alsarya.tv << 'EOF'
   cd /home/alsarya.tv/public_html
   php artisan tinker
   use App\Models\Caller;
   Caller::where('cpr', '123456789')->first();
   EOF
   
   Expected: Caller record with name "Test User"

3. Run PEST Tests
   ─────────────────────────────────────────────────────────────────────
   php artisan test tests/Feature/CallerRegistrationSecurityTest.php
   
   Expected: All tests PASS ✓

4. Monitor Logs
   ─────────────────────────────────────────────────────────────────────
   # On production server:
   tail -f /home/alsarya.tv/public_html/storage/logs/laravel.log
   tail -f /home/alsarya.tv/public_html/storage/logs/deployments/deploy_*.log


MONITORING AFTER DEPLOYMENT:
═══════════════════════════════════════════════════════════════════════════

Deployment Logs Location:

  Local:       ./storage/logs/deployments/
  Production:  /home/alsarya.tv/public_html/storage/logs/deployments/

Performance Metrics:

  cat storage/logs/deployments/deploy_performance.log

  Format:
  HH:MM:SS|command|STATUS|duration|exit_code

  Example:
  22:45:35|git fetch origin|SUCCESS|10.2s
  22:45:45|php artisan migrate|SUCCESS|13.5s

Errors:

  grep "ERROR\|FAILED" storage/logs/deployments/deploy_*.log


ROLLBACK INSTRUCTIONS (If Needed):
═══════════════════════════════════════════════════════════════════════════

Quick Rollback:

  ssh root@alsarya.tv << 'EOF'
  cd /home/alsarya.tv/public_html
  git revert HEAD
  git push origin main
  ./deploy.sh --force
  EOF

Database Rollback:

  # Find backup
  ls /home/alsarya.tv/backups/pre_fix_*/
  
  # Restore SQLite
  cp /home/alsarya.tv/database.sqlite /home/alsarya.tv/database.sqlite.recover
  
  # Or restore MySQL
  mysql -u user -p database < /home/alsarya.tv/backups/pre_fix_*/database.sql


CRITICAL NOTES:
═══════════════════════════════════════════════════════════════════════════

⚠️  DO NOT:
   • Edit vendor files directly
   • Force push to main branch
   • Skip the test verification step
   • Deploy during peak traffic if possible

✅ DO:
   • Review logs after deployment
   • Test registration form manually
   • Monitor error logs for 30 minutes
   • Keep the deployment script for reference

📋 READ THESE FILES FIRST:
   1. IMPLEMENTATION_SUMMARY.md (complete technical guide)
   2. REGISTRATION_FIX_DEPLOYMENT.md (manual deployment steps)


NEXT STEPS:
═══════════════════════════════════════════════════════════════════════════

1. Choose deployment option above

2. Run deployment command

3. Wait for completion (3-5 minutes)

4. Check logs:
   tail -f storage/logs/deployments/deploy_*.log

5. Test registration:
   https://alsarya.tv

6. Monitor application:
   ssh root@alsarya.tv "tail -f /home/alsarya.tv/public_html/storage/logs/laravel.log"


SUPPORT:
═══════════════════════════════════════════════════════════════════════════

If registration still fails after deployment:

1. Check logs:
   storage/logs/deployments/deploy_*.log (what went wrong)
   storage/logs/laravel.log (runtime errors)

2. Verify the fix:
   grep "Allow public caller registration" app/Models/Caller.php

3. Check PHP compatibility:
   php -v (should be 8.5+)

4. Rollback if necessary (see instructions above)

5. Report issues with full log content


═══════════════════════════════════════════════════════════════════════════

Ready? Let's deploy! 🚀

  cd /Users/aldoyh/Sites/RAMADAN/alsaryatv
  ./publish.sh --force

═══════════════════════════════════════════════════════════════════════════

EOF
