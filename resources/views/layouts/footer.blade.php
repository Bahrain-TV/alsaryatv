<footer id="footer" class="w-full text-white py-2 sm:py-3 z-10 rtl"
        style="background: rgba(0,0,0,0.72); backdrop-filter: blur(8px); border-top: 1px solid rgba(168,28,46,0.25);">
    <div class="container mx-auto px-4">
        <!-- Single-row with tight spacing -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-2 sm:gap-4 text-xs sm:text-sm">

            <!-- Left: Branding -->
            <div class="text-center sm:text-start">
                <p class="font-bold" style="color: #F5DEB3; letter-spacing: 0.5px;">
                    {{ config('app.ar_translations.footer_title') ?? 'برنامج الســارية ©️ ' . date('Y') }}
                </p>
            </div>

            <!-- Center: Live stats -->
            @if(isset($hits))
            <div class="flex gap-3 justify-center items-center opacity-90">
                <span style="color: #F5DEB3;">المشاركات: <strong>{{ number_format($totalHits ?? 0) }}</strong></span>
                <span style="color: rgba(168,28,46,0.6);">•</span>
                <span style="color: #F5DEB3;">الزيارات: <strong>{{ number_format($hits ?? 0) }}</strong></span>
            </div>
            @endif

            <!-- Right: Links -->
            <div class="flex flex-wrap gap-2 sm:gap-3 justify-center sm:justify-end items-center">
                <a href="{{ route('privacy') }}"
                   class="transition-colors"
                   style="color: rgba(245,222,179,0.6);"
                   onmouseover="this.style.color='#F5DEB3'" onmouseout="this.style.color='rgba(245,222,179,0.6)'">
                    سياسة الخصوصية
                </a>
                <span style="color: rgba(168,28,46,0.5);">•</span>
                <a href="{{ route('terms') }}"
                   class="transition-colors"
                   style="color: rgba(245,222,179,0.6);"
                   onmouseover="this.style.color='#F5DEB3'" onmouseout="this.style.color='rgba(245,222,179,0.6)'">
                    شروط الاستخدام
                </a>
                <span style="color: rgba(168,28,46,0.5);">•</span>
                <a href="{{ route('policy') }}"
                   class="transition-colors"
                   style="color: rgba(245,222,179,0.6);"
                   onmouseover="this.style.color='#F5DEB3'" onmouseout="this.style.color='rgba(245,222,179,0.6)'">
                    الشروط والأحكام
                </a>
                <span style="color: rgba(168,28,46,0.5);">•</span>
                <span style="color: rgba(245,222,179,0.35); font-size: 0.6rem; font-family: monospace;">
                    {{ config('app.version', 'v1.0') }}
                </span>
            </div>
        </div>

        <!-- Bottom copyright with BTV logo -->
        <div class="mt-1.5 pt-1.5 flex flex-col sm:flex-row items-center justify-center gap-1.5 sm:gap-3" style="border-top: 1px solid rgba(255,255,255,0.06);">
            <!-- BTV Logo -->
            <div class="flex items-center justify-center">
                <img src="{{ asset('images/btv-logo-ar.png') }}" 
                     alt="تلفزيون البحرين" 
                     class="h-4 w-auto opacity-60 hover:opacity-100 transition-opacity duration-200"
                     style="filter: brightness(1.1);">
            </div>
            
            <!-- Copyright text -->
            <p style="font-size: 0.65rem; color: rgba(255,255,255,0.35);">
                تصميم وبرمجة فريق عمل برنامج السارية &nbsp;|&nbsp; تلفزيون البحرين — وزارة الإعلام
            </p>
        </div>
    </div>
</footer>
