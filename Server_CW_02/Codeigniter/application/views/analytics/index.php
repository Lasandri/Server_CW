<!-- Analytics Overview Page -->
<p style="color:#888;margin-bottom:24px;">
    Select an analytics section to view detailed charts and insights.
</p>

<div style="display:grid;
            grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
            gap:20px;">

    <a href="<?php echo site_url('analytics/skills-gap'); ?>"
       style="text-decoration:none;">
        <div class="card" style="cursor:pointer;
             transition:transform 0.2s,box-shadow 0.2s;"
             onmouseover="this.style.transform='translateY(-4px)';
                          this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
             onmouseout="this.style.transform='';
                         this.style.boxShadow=''">
            <div class="card-body" style="text-align:center;padding:32px;">
                <div style="font-size:48px;margin-bottom:12px;">🎯</div>
                <h3 style="color:#1565C0;margin-bottom:8px;">Skills Gap</h3>
                <p style="color:#888;font-size:13px;">
                    Analyse certifications, courses and licences
                    acquired post-graduation
                </p>
            </div>
        </div>
    </a>

    <a href="<?php echo site_url('analytics/employment'); ?>"
       style="text-decoration:none;">
        <div class="card" style="cursor:pointer;
             transition:transform 0.2s,box-shadow 0.2s;"
             onmouseover="this.style.transform='translateY(-4px)';
                          this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
             onmouseout="this.style.transform='';
                         this.style.boxShadow=''">
            <div class="card-body" style="text-align:center;padding:32px;">
                <div style="font-size:48px;margin-bottom:12px;">💼</div>
                <h3 style="color:#1565C0;margin-bottom:8px;">Employment</h3>
                <p style="color:#888;font-size:13px;">
                    Employment distribution by industry sector
                </p>
            </div>
        </div>
    </a>

    <a href="<?php echo site_url('analytics/job-titles'); ?>"
       style="text-decoration:none;">
        <div class="card" style="cursor:pointer;
             transition:transform 0.2s,box-shadow 0.2s;"
             onmouseover="this.style.transform='translateY(-4px)';
                          this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
             onmouseout="this.style.transform='';
                         this.style.boxShadow=''">
            <div class="card-body" style="text-align:center;padding:32px;">
                <div style="font-size:48px;margin-bottom:12px;">🏷️</div>
                <h3 style="color:#1565C0;margin-bottom:8px;">Job Titles</h3>
                <p style="color:#888;font-size:13px;">
                    Most common job titles among alumni
                </p>
            </div>
        </div>
    </a>

    <a href="<?php echo site_url('analytics/employers'); ?>"
       style="text-decoration:none;">
        <div class="card" style="cursor:pointer;
             transition:transform 0.2s,box-shadow 0.2s;"
             onmouseover="this.style.transform='translateY(-4px)';
                          this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
             onmouseout="this.style.transform='';
                         this.style.boxShadow=''">
            <div class="card-body" style="text-align:center;padding:32px;">
                <div style="font-size:48px;margin-bottom:12px;">🏢</div>
                <h3 style="color:#1565C0;margin-bottom:8px;">Top Employers</h3>
                <p style="color:#888;font-size:13px;">
                    Companies hiring the most alumni
                </p>
            </div>
        </div>
    </a>

    <a href="<?php echo site_url('analytics/geographic'); ?>"
       style="text-decoration:none;">
        <div class="card" style="cursor:pointer;
             transition:transform 0.2s,box-shadow 0.2s;"
             onmouseover="this.style.transform='translateY(-4px)';
                          this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
             onmouseout="this.style.transform='';
                         this.style.boxShadow=''">
            <div class="card-body" style="text-align:center;padding:32px;">
                <div style="font-size:48px;margin-bottom:12px;">🌍</div>
                <h3 style="color:#1565C0;margin-bottom:8px;">Geographic</h3>
                <p style="color:#888;font-size:13px;">
                    Where alumni are located worldwide
                </p>
            </div>
        </div>
    </a>

    <a href="<?php echo site_url('analytics/trends'); ?>"
       style="text-decoration:none;">
        <div class="card" style="cursor:pointer;
             transition:transform 0.2s,box-shadow 0.2s;"
             onmouseover="this.style.transform='translateY(-4px)';
                          this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
             onmouseout="this.style.transform='';
                         this.style.boxShadow=''">
            <div class="card-body" style="text-align:center;padding:32px;">
                <div style="font-size:48px;margin-bottom:12px;">📅</div>
                <h3 style="color:#1565C0;margin-bottom:8px;">Trends</h3>
                <p style="color:#888;font-size:13px;">
                    Certification and course trends over time
                </p>
            </div>
        </div>
    </a>

</div>