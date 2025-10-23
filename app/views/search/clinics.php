<section class="cl-hero">
    <div class="cl-wrap">
        <form class="search" method="get" action="<?= BASE_URL ?>index.php">
            <input type="hidden" name="page" value="clinics">
            <div class="search__field">
                <label>Doctor, Clinic or Specialty</label>
                <input type="text" name="q" placeholder="e.g., Dr. Clark, Cardiology, …">
            </div>
            <div class="search__field">
                <label>Location</label>
                <input type="text" name="loc" placeholder="Where ?">
            </div>
            <button class="search__btn" type="submit">Search</button>
        </form>
    </div>
</section>

<section class="cl-results">
    <div class="cl-wrap">
        <div class="cl-count">4 results</div>

        <!-- Card 1 -->
        <article class="clinic">
            <div class="clinic__row">
                <div class="clinic__left">
                    <div class="clinic__logo">BH</div>
                    <div class="clinic__meta">
                        <h3 class="clinic__name">Blue Horizon Medical Center</h3>
                        <p class="clinic__addr">142 King’s Road, London, UK</p>
                        <p class="clinic__status is-open"><span class="dot"></span> Open · Closes 5:00pm</p>
                    </div>
                </div>
                <a class="clinic__cta" href="<?= BASE_URL ?>index.php?page=clinic&id=1">See</a>
            </div>

            <div class="clinic__slots">
                <span class="day">Today</span>
                <a href="#" class="slot">10:30am</a>
                <a href="#" class="slot">11:00am</a>
                <a href="#" class="slot">11:30am</a>
                <a href="#" class="slot">1:00pm</a>
                <a href="#" class="slot">1:30pm</a>
                <a href="#" class="slot">4:00pm</a>
                <a href="#" class="more">+ more</a>
            </div>
        </article>

        <!-- Card 2 -->
        <article class="clinic">
            <div class="clinic__row">
                <div class="clinic__left">
                    <div class="clinic__logo">GH</div>
                    <div class="clinic__meta">
                        <h3 class="clinic__name">Green Health Clinic</h3>
                        <p class="clinic__addr">25 Elm Street, London, UK</p>
                        <p class="clinic__status is-closed"><span class="dot"></span> Closed · Opens Mon 8:30am</p>
                    </div>
                </div>
                <button class="clinic__cta" type="button">See</button>
            </div>

            <div class="clinic__slots">
                <span class="day">Monday</span>
                <a href="#" class="slot">10:30am</a><a href="#" class="slot">11:00am</a>
                <a href="#" class="slot">11:30am</a><a href="#" class="slot">1:00pm</a>
                <a href="#" class="slot">1:30pm</a><a href="#" class="slot">4:00pm</a>
                <a href="#" class="more">+ more</a>
            </div>
        </article>

        <!-- Card 3 -->
        <article class="clinic">
            <div class="clinic__row">
                <div class="clinic__left">
                    <div class="clinic__logo">SF</div>
                    <div class="clinic__meta">
                        <h3 class="clinic__name">Sunrise Family Care</h3>
                        <p class="clinic__addr">78 Maple Avenue, London, UK</p>
                        <p class="clinic__status is-closed"><span class="dot"></span> Closed · Opens Mon 8:30am</p>
                    </div>
                </div>
                <button class="clinic__cta" type="button">See</button>
            </div>

            <div class="clinic__slots">
                <span class="day">Monday</span>
                <a href="#" class="slot">10:30am</a><a href="#" class="slot">11:00am</a>
                <a href="#" class="slot">11:30am</a><a href="#" class="slot">1:00pm</a>
                <a href="#" class="slot">1:30pm</a><a href="#" class="slot">4:00pm</a>
                <a href="#" class="more">+ more</a>
            </div>
        </article>

        <!-- Card 4 -->
        <article class="clinic">
            <div class="clinic__row">
                <div class="clinic__left">
                    <div class="clinic__logo">RM</div>
                    <div class="clinic__meta">
                        <h3 class="clinic__name">Riverside Medical Practice</h3>
                        <p class="clinic__addr">310 Bridge Street, London, UK</p>
                        <p class="clinic__status is-closed"><span class="dot"></span> Closed · Opens Mon 8:30am</p>
                    </div>
                </div>
                <button class="clinic__cta" type="button">See</button>
            </div>

            <div class="clinic__slots">
                <span class="day">Monday</span>
                <a href="#" class="slot">10:30am</a><a href="#" class="slot">11:00am</a>
                <a href="#" class="slot">11:30am</a><a href="#" class="slot">1:00pm</a>
                <a href="#" class="slot">1:30pm</a><a href="#" class="slot">4:00pm</a>
                <a href="#" class="more">+ more</a>
            </div>
        </article>
    </div>
</section>