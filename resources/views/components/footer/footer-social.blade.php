<ul class="social-icon">
    <!-- facebook -->
    <li class="social-icon__item">
        <a href="{{ $settings->facebook }}" class="social-icon__link">
            <x-svg.facebook-icon fill="currentColor"/>
        </a>
    </li>

    <!-- Twitter -->
    <li class="social-icon__item">
        <a href="{{ $settings->twitter }}" class="social-icon__link">
            <x-svg.twitter-icon fill="currentColor" />
        </a>
    </li>

    <!-- Instagram -->
    <li class="social-icon__item">
        <a href="{{ $settings->instagram }}" class="social-icon__link">
            <x-svg.instagram-icon />
        </a>
    </li>

    <!-- Youtube -->
    <li class="social-icon__item">
        <a href="{{ $settings->youtube }}" class="social-icon__link">
           <x-svg.youtube-icon />
        </a>
    </li>

    <!-- Linkedin -->
    <li class="social-icon__item">
        <a href="{{ $settings->linkdin }}" class="social-icon__link">
            <x-svg.linkedin-footer-icon />
        </a>
    </li>

    <!-- whats app -->
    <li class="social-icon__item">
        <a href="{{ $settings->whatsapp }}" class="social-icon__link">
            <x-svg.whatsapp-footer-icon />
        </a>
    </li>
</ul>
