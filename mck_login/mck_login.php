  


<!DOCTYPE html>
<html>
  <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# githubog: http://ogp.me/ns/fb/githubog#">
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>mck_login/mck_login.php at master · gocom/mck_login · GitHub</title>
    <link rel="search" type="application/opensearchdescription+xml" href="/opensearch.xml" title="GitHub" />
    <link rel="fluid-icon" href="https://github.com/fluidicon.png" title="GitHub" />
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-114.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-144.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144.png" />
    <link rel="logo" type="image/svg" href="http://github-media-downloads.s3.amazonaws.com/github-logo.svg" />
    <link rel="xhr-socket" href="/_sockets" />


    <meta name="msapplication-TileImage" content="/windows-tile.png" />
    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="selected-link" value="repo_source" data-pjax-transient />
    

    
    
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />

    <meta content="authenticity_token" name="csrf-param" />
<meta content="dTICH0gRYWrCPhfgUjUoZSPhyOS5VJKspPinwEZVshc=" name="csrf-token" />

    <link href="https://a248.e.akamai.net/assets.github.com/assets/github-fdebe8d3f60746fb87c763a59741ff520ae3d8e8.css" media="all" rel="stylesheet" type="text/css" />
    <link href="https://a248.e.akamai.net/assets.github.com/assets/github2-d530e63e2c132c7f0e6ac7228e7e1ab9ef2a8d94.css" media="all" rel="stylesheet" type="text/css" />
    


      <script src="https://a248.e.akamai.net/assets.github.com/assets/frameworks-92d138f450f2960501e28397a2f63b0f100590f0.js" type="text/javascript"></script>
      <script src="https://a248.e.akamai.net/assets.github.com/assets/github-4037f12703c2d563310be4fcd52a229189468cce.js" type="text/javascript"></script>
      
      <meta http-equiv="x-pjax-version" content="80973bfd2a5cb09c53d745b26b5b1dc0">

        <link data-pjax-transient rel='permalink' href='/gocom/mck_login/blob/8c6aa5770c30d23798e40d5dd1e162d25520e617/mck_login.php'>
    <meta property="og:title" content="mck_login"/>
    <meta property="og:type" content="githubog:gitrepository"/>
    <meta property="og:url" content="https://github.com/gocom/mck_login"/>
    <meta property="og:image" content="https://secure.gravatar.com/avatar/9271a98d6fec11834bc42aeefc87e871?s=420&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png"/>
    <meta property="og:site_name" content="GitHub"/>
    <meta property="og:description" content="Patch/fork of mck_login, a Texpattern plugin"/>
    <meta property="twitter:card" content="summary"/>
    <meta property="twitter:site" content="@GitHub">
    <meta property="twitter:title" content="gocom/mck_login"/>

    <meta name="description" content="Patch/fork of mck_login, a Texpattern plugin" />


    
  <link href="https://github.com/gocom/mck_login/commits/master.atom" rel="alternate" title="Recent Commits to mck_login:master" type="application/atom+xml" />

  </head>


  <body class="logged_out page-blob macintosh vis-public env-production  ">
    <div id="wrapper">

      

      
      
      

      
      <div class="header header-logged-out">
  <div class="container clearfix">

    <a class="header-logo-wordmark" href="https://github.com/">Github</a>

    <div class="header-actions">
      <a class="button primary" href="https://github.com/signup">Sign up</a>
      <a class="button" href="https://github.com/login?return_to=%2Fgocom%2Fmck_login%2Fblob%2Fmaster%2Fmck_login.php">Sign in</a>
    </div>

    <div class="command-bar js-command-bar  in-repository">


      <ul class="top-nav">
          <li class="explore"><a href="https://github.com/explore">Explore</a></li>
        <li class="features"><a href="https://github.com/features">Features</a></li>
          <li class="blog"><a href="https://github.com/blog">Blog</a></li>
      </ul>
        <form accept-charset="UTF-8" action="/search" class="command-bar-form" id="top_search_form" method="get">
  <a href="/search/advanced" class="advanced-search-icon tooltipped downwards command-bar-search" id="advanced_search" title="Advanced search"><span class="mini-icon mini-icon-advanced-search "></span></a>

  <input type="text" data-hotkey="/ s" name="q" id="js-command-bar-field" placeholder="Search or type a command" tabindex="1" autocapitalize="off"
    
      data-repo="gocom/mck_login"
      data-branch="master"
      data-sha="35675a05940468a4653be3f5dc36310675e6509f"
  >

    <input type="hidden" name="nwo" value="gocom/mck_login" />

    <div class="select-menu js-menu-container js-select-menu search-context-select-menu">
      <span class="minibutton select-menu-button js-menu-target">
        <span class="js-select-button">This repository</span>
      </span>

      <div class="select-menu-modal-holder js-menu-content js-navigation-container">
        <div class="select-menu-modal">

          <div class="select-menu-item js-navigation-item selected">
            <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
            <input type="radio" class="js-search-this-repository" name="search_target" value="repository" checked="checked" />
            <div class="select-menu-item-text js-select-button-text">This repository</div>
          </div> <!-- /.select-menu-item -->

          <div class="select-menu-item js-navigation-item">
            <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
            <input type="radio" name="search_target" value="global" />
            <div class="select-menu-item-text js-select-button-text">All repositories</div>
          </div> <!-- /.select-menu-item -->

        </div>
      </div>
    </div>

  <span class="mini-icon help tooltipped downwards" title="Show command bar help">
    <span class="mini-icon mini-icon-help"></span>
  </span>

    <input type="hidden" name="type" value="Code" />

  <input type="hidden" name="ref" value="cmdform">

  <div class="divider-vertical"></div>

</form>
    </div>

  </div>
</div>


      

      


            <div class="site hfeed" itemscope itemtype="http://schema.org/WebPage">
      <div class="hentry">
        
        <div class="pagehead repohead instapaper_ignore readability-menu ">
          <div class="container">
            <div class="title-actions-bar">
              

<ul class="pagehead-actions">



    <li>
      <a href="/login?return_to=%2Fgocom%2Fmck_login"
        class="minibutton js-toggler-target star-button entice tooltipped upwards"
        title="You must be signed in to use this feature" rel="nofollow">
        <span class="mini-icon mini-icon-star"></span>Star
      </a>
      <a class="social-count js-social-count" href="/gocom/mck_login/stargazers">
        1
      </a>
    </li>
    <li>
      <a href="/login?return_to=%2Fgocom%2Fmck_login"
        class="minibutton js-toggler-target fork-button entice tooltipped upwards"
        title="You must be signed in to fork a repository" rel="nofollow">
        <span class="mini-icon mini-icon-fork"></span>Fork
      </a>
      <a href="/gocom/mck_login/network" class="social-count">
        0
      </a>
    </li>
</ul>

              <h1 itemscope itemtype="http://data-vocabulary.org/Breadcrumb" class="entry-title public">
                <span class="repo-label"><span>public</span></span>
                <span class="mega-icon mega-icon-public-repo"></span>
                <span class="author vcard">
                  <a href="/gocom" class="url fn" itemprop="url" rel="author">
                  <span itemprop="title">gocom</span>
                  </a></span> /
                <strong><a href="/gocom/mck_login" class="js-current-repository">mck_login</a></strong>
              </h1>
            </div>

            
  <ul class="tabs">
    <li class="pulse-nav"><a href="/gocom/mck_login/pulse" class="js-selected-navigation-item " data-selected-links="pulse /gocom/mck_login/pulse" rel="nofollow"><span class="mini-icon mini-icon-pulse"></span></a></li>
    <li><a href="/gocom/mck_login" class="js-selected-navigation-item selected" data-selected-links="repo_source repo_downloads repo_commits repo_tags repo_branches /gocom/mck_login">Code</a></li>
    <li><a href="/gocom/mck_login/network" class="js-selected-navigation-item " data-selected-links="repo_network /gocom/mck_login/network">Network</a></li>
    <li><a href="/gocom/mck_login/pulls" class="js-selected-navigation-item " data-selected-links="repo_pulls /gocom/mck_login/pulls">Pull Requests <span class='counter'>0</span></a></li>

      <li><a href="/gocom/mck_login/issues" class="js-selected-navigation-item " data-selected-links="repo_issues /gocom/mck_login/issues">Issues <span class='counter'>0</span></a></li>



    <li><a href="/gocom/mck_login/graphs" class="js-selected-navigation-item " data-selected-links="repo_graphs repo_contributors /gocom/mck_login/graphs">Graphs</a></li>


  </ul>
  
<div class="tabnav">

  <span class="tabnav-right">
    <ul class="tabnav-tabs">
          <li><a href="/gocom/mck_login/tags" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_tags /gocom/mck_login/tags">Tags <span class="counter blank">0</span></a></li>
    </ul>
  </span>

  <div class="tabnav-widget scope">


    <div class="select-menu js-menu-container js-select-menu js-branch-menu">
      <a class="minibutton select-menu-button js-menu-target" data-hotkey="w" data-ref="master">
        <span class="mini-icon mini-icon-branch"></span>
        <i>branch:</i>
        <span class="js-select-button">master</span>
      </a>

      <div class="select-menu-modal-holder js-menu-content js-navigation-container">

        <div class="select-menu-modal">
          <div class="select-menu-header">
            <span class="select-menu-title">Switch branches/tags</span>
            <span class="mini-icon mini-icon-remove-close js-menu-close"></span>
          </div> <!-- /.select-menu-header -->

          <div class="select-menu-filters">
            <div class="select-menu-text-filter">
              <input type="text" id="commitish-filter-field" class="js-filterable-field js-navigation-enable" placeholder="Filter branches/tags">
            </div>
            <div class="select-menu-tabs">
              <ul>
                <li class="select-menu-tab">
                  <a href="#" data-tab-filter="branches" class="js-select-menu-tab">Branches</a>
                </li>
                <li class="select-menu-tab">
                  <a href="#" data-tab-filter="tags" class="js-select-menu-tab">Tags</a>
                </li>
              </ul>
            </div><!-- /.select-menu-tabs -->
          </div><!-- /.select-menu-filters -->

          <div class="select-menu-list select-menu-tab-bucket js-select-menu-tab-bucket css-truncate" data-tab-filter="branches">

            <div data-filterable-for="commitish-filter-field" data-filterable-type="substring">

                <div class="select-menu-item js-navigation-item selected">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/gocom/mck_login/blob/master/mck_login.php" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="master" rel="nofollow" title="master">master</a>
                </div> <!-- /.select-menu-item -->
            </div>

              <div class="select-menu-no-results">Nothing to show</div>
          </div> <!-- /.select-menu-list -->


          <div class="select-menu-list select-menu-tab-bucket js-select-menu-tab-bucket css-truncate" data-tab-filter="tags">
            <div data-filterable-for="commitish-filter-field" data-filterable-type="substring">

            </div>

            <div class="select-menu-no-results">Nothing to show</div>

          </div> <!-- /.select-menu-list -->

        </div> <!-- /.select-menu-modal -->
      </div> <!-- /.select-menu-modal-holder -->
    </div> <!-- /.select-menu -->

  </div> <!-- /.scope -->

  <ul class="tabnav-tabs">
    <li><a href="/gocom/mck_login" class="selected js-selected-navigation-item tabnav-tab" data-selected-links="repo_source /gocom/mck_login">Files</a></li>
    <li><a href="/gocom/mck_login/commits/master" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_commits /gocom/mck_login/commits/master">Commits</a></li>
    <li><a href="/gocom/mck_login/branches" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_branches /gocom/mck_login/branches" rel="nofollow">Branches <span class="counter ">1</span></a></li>
  </ul>

</div>

  
  
  


            
          </div>
        </div><!-- /.repohead -->

        <div id="js-repo-pjax-container" class="container context-loader-container" data-pjax-container>
          


<!-- blob contrib key: blob_contributors:v21:0de41cf73b90fa17f96edfb5917186e6 -->
<!-- blob contrib frag key: views10/v8/blob_contributors:v21:0de41cf73b90fa17f96edfb5917186e6 -->


<div id="slider">
    <div class="frame-meta">

      <p title="This is a placeholder element" class="js-history-link-replace hidden"></p>

        <div class="breadcrumb">
          <span class='bold'><span itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/gocom/mck_login" class="js-slide-to" data-branch="master" data-direction="back" itemscope="url"><span itemprop="title">mck_login</span></a></span></span><span class="separator"> / </span><strong class="final-path">mck_login.php</strong> <span class="js-zeroclipboard zeroclipboard-button" data-clipboard-text="mck_login.php" data-copied-hint="copied!" title="copy to clipboard"><span class="mini-icon mini-icon-clipboard"></span></span>
        </div>

      <a href="/gocom/mck_login/find/master" class="js-slide-to" data-hotkey="t" style="display:none">Show File Finder</a>


        <div class="commit commit-loader file-history-tease js-deferred-content" data-url="/gocom/mck_login/contributors/master/mck_login.php">
          Fetching contributors…

          <div class="participation">
            <p class="loader-loading"><img alt="Octocat-spinner-32-eaf2f5" height="16" src="https://a248.e.akamai.net/assets.github.com/images/spinners/octocat-spinner-32-EAF2F5.gif?1360648843" width="16" /></p>
            <p class="loader-error">Cannot retrieve contributors at this time</p>
          </div>
        </div>

    </div><!-- ./.frame-meta -->

    <div class="frames">
      <div class="frame" data-permalink-url="/gocom/mck_login/blob/8c6aa5770c30d23798e40d5dd1e162d25520e617/mck_login.php" data-title="mck_login/mck_login.php at master · gocom/mck_login · GitHub" data-type="blob">

        <div id="files" class="bubble">
          <div class="file">
            <div class="meta">
              <div class="info">
                <span class="icon"><b class="mini-icon mini-icon-text-file"></b></span>
                <span class="mode" title="File Mode">file</span>
                  <span>1064 lines (818 sloc)</span>
                <span>24.194 kb</span>
              </div>
              <div class="actions">
                <div class="button-group">
                      <a class="minibutton js-entice" href=""
                         data-entice="You must be signed in and on a branch to make or propose changes">Edit</a>
                  <a href="/gocom/mck_login/raw/master/mck_login.php" class="button minibutton " id="raw-url">Raw</a>
                    <a href="/gocom/mck_login/blame/master/mck_login.php" class="button minibutton ">Blame</a>
                  <a href="/gocom/mck_login/commits/master/mck_login.php" class="button minibutton " rel="nofollow">History</a>
                </div><!-- /.button-group -->
              </div><!-- /.actions -->

            </div>
                <div class="blob-wrapper data type-php js-blob-data">
      <table class="file-code file-diff">
        <tr class="file-code-line">
          <td class="blob-line-nums">
            <span id="L1" rel="#L1">1</span>
<span id="L2" rel="#L2">2</span>
<span id="L3" rel="#L3">3</span>
<span id="L4" rel="#L4">4</span>
<span id="L5" rel="#L5">5</span>
<span id="L6" rel="#L6">6</span>
<span id="L7" rel="#L7">7</span>
<span id="L8" rel="#L8">8</span>
<span id="L9" rel="#L9">9</span>
<span id="L10" rel="#L10">10</span>
<span id="L11" rel="#L11">11</span>
<span id="L12" rel="#L12">12</span>
<span id="L13" rel="#L13">13</span>
<span id="L14" rel="#L14">14</span>
<span id="L15" rel="#L15">15</span>
<span id="L16" rel="#L16">16</span>
<span id="L17" rel="#L17">17</span>
<span id="L18" rel="#L18">18</span>
<span id="L19" rel="#L19">19</span>
<span id="L20" rel="#L20">20</span>
<span id="L21" rel="#L21">21</span>
<span id="L22" rel="#L22">22</span>
<span id="L23" rel="#L23">23</span>
<span id="L24" rel="#L24">24</span>
<span id="L25" rel="#L25">25</span>
<span id="L26" rel="#L26">26</span>
<span id="L27" rel="#L27">27</span>
<span id="L28" rel="#L28">28</span>
<span id="L29" rel="#L29">29</span>
<span id="L30" rel="#L30">30</span>
<span id="L31" rel="#L31">31</span>
<span id="L32" rel="#L32">32</span>
<span id="L33" rel="#L33">33</span>
<span id="L34" rel="#L34">34</span>
<span id="L35" rel="#L35">35</span>
<span id="L36" rel="#L36">36</span>
<span id="L37" rel="#L37">37</span>
<span id="L38" rel="#L38">38</span>
<span id="L39" rel="#L39">39</span>
<span id="L40" rel="#L40">40</span>
<span id="L41" rel="#L41">41</span>
<span id="L42" rel="#L42">42</span>
<span id="L43" rel="#L43">43</span>
<span id="L44" rel="#L44">44</span>
<span id="L45" rel="#L45">45</span>
<span id="L46" rel="#L46">46</span>
<span id="L47" rel="#L47">47</span>
<span id="L48" rel="#L48">48</span>
<span id="L49" rel="#L49">49</span>
<span id="L50" rel="#L50">50</span>
<span id="L51" rel="#L51">51</span>
<span id="L52" rel="#L52">52</span>
<span id="L53" rel="#L53">53</span>
<span id="L54" rel="#L54">54</span>
<span id="L55" rel="#L55">55</span>
<span id="L56" rel="#L56">56</span>
<span id="L57" rel="#L57">57</span>
<span id="L58" rel="#L58">58</span>
<span id="L59" rel="#L59">59</span>
<span id="L60" rel="#L60">60</span>
<span id="L61" rel="#L61">61</span>
<span id="L62" rel="#L62">62</span>
<span id="L63" rel="#L63">63</span>
<span id="L64" rel="#L64">64</span>
<span id="L65" rel="#L65">65</span>
<span id="L66" rel="#L66">66</span>
<span id="L67" rel="#L67">67</span>
<span id="L68" rel="#L68">68</span>
<span id="L69" rel="#L69">69</span>
<span id="L70" rel="#L70">70</span>
<span id="L71" rel="#L71">71</span>
<span id="L72" rel="#L72">72</span>
<span id="L73" rel="#L73">73</span>
<span id="L74" rel="#L74">74</span>
<span id="L75" rel="#L75">75</span>
<span id="L76" rel="#L76">76</span>
<span id="L77" rel="#L77">77</span>
<span id="L78" rel="#L78">78</span>
<span id="L79" rel="#L79">79</span>
<span id="L80" rel="#L80">80</span>
<span id="L81" rel="#L81">81</span>
<span id="L82" rel="#L82">82</span>
<span id="L83" rel="#L83">83</span>
<span id="L84" rel="#L84">84</span>
<span id="L85" rel="#L85">85</span>
<span id="L86" rel="#L86">86</span>
<span id="L87" rel="#L87">87</span>
<span id="L88" rel="#L88">88</span>
<span id="L89" rel="#L89">89</span>
<span id="L90" rel="#L90">90</span>
<span id="L91" rel="#L91">91</span>
<span id="L92" rel="#L92">92</span>
<span id="L93" rel="#L93">93</span>
<span id="L94" rel="#L94">94</span>
<span id="L95" rel="#L95">95</span>
<span id="L96" rel="#L96">96</span>
<span id="L97" rel="#L97">97</span>
<span id="L98" rel="#L98">98</span>
<span id="L99" rel="#L99">99</span>
<span id="L100" rel="#L100">100</span>
<span id="L101" rel="#L101">101</span>
<span id="L102" rel="#L102">102</span>
<span id="L103" rel="#L103">103</span>
<span id="L104" rel="#L104">104</span>
<span id="L105" rel="#L105">105</span>
<span id="L106" rel="#L106">106</span>
<span id="L107" rel="#L107">107</span>
<span id="L108" rel="#L108">108</span>
<span id="L109" rel="#L109">109</span>
<span id="L110" rel="#L110">110</span>
<span id="L111" rel="#L111">111</span>
<span id="L112" rel="#L112">112</span>
<span id="L113" rel="#L113">113</span>
<span id="L114" rel="#L114">114</span>
<span id="L115" rel="#L115">115</span>
<span id="L116" rel="#L116">116</span>
<span id="L117" rel="#L117">117</span>
<span id="L118" rel="#L118">118</span>
<span id="L119" rel="#L119">119</span>
<span id="L120" rel="#L120">120</span>
<span id="L121" rel="#L121">121</span>
<span id="L122" rel="#L122">122</span>
<span id="L123" rel="#L123">123</span>
<span id="L124" rel="#L124">124</span>
<span id="L125" rel="#L125">125</span>
<span id="L126" rel="#L126">126</span>
<span id="L127" rel="#L127">127</span>
<span id="L128" rel="#L128">128</span>
<span id="L129" rel="#L129">129</span>
<span id="L130" rel="#L130">130</span>
<span id="L131" rel="#L131">131</span>
<span id="L132" rel="#L132">132</span>
<span id="L133" rel="#L133">133</span>
<span id="L134" rel="#L134">134</span>
<span id="L135" rel="#L135">135</span>
<span id="L136" rel="#L136">136</span>
<span id="L137" rel="#L137">137</span>
<span id="L138" rel="#L138">138</span>
<span id="L139" rel="#L139">139</span>
<span id="L140" rel="#L140">140</span>
<span id="L141" rel="#L141">141</span>
<span id="L142" rel="#L142">142</span>
<span id="L143" rel="#L143">143</span>
<span id="L144" rel="#L144">144</span>
<span id="L145" rel="#L145">145</span>
<span id="L146" rel="#L146">146</span>
<span id="L147" rel="#L147">147</span>
<span id="L148" rel="#L148">148</span>
<span id="L149" rel="#L149">149</span>
<span id="L150" rel="#L150">150</span>
<span id="L151" rel="#L151">151</span>
<span id="L152" rel="#L152">152</span>
<span id="L153" rel="#L153">153</span>
<span id="L154" rel="#L154">154</span>
<span id="L155" rel="#L155">155</span>
<span id="L156" rel="#L156">156</span>
<span id="L157" rel="#L157">157</span>
<span id="L158" rel="#L158">158</span>
<span id="L159" rel="#L159">159</span>
<span id="L160" rel="#L160">160</span>
<span id="L161" rel="#L161">161</span>
<span id="L162" rel="#L162">162</span>
<span id="L163" rel="#L163">163</span>
<span id="L164" rel="#L164">164</span>
<span id="L165" rel="#L165">165</span>
<span id="L166" rel="#L166">166</span>
<span id="L167" rel="#L167">167</span>
<span id="L168" rel="#L168">168</span>
<span id="L169" rel="#L169">169</span>
<span id="L170" rel="#L170">170</span>
<span id="L171" rel="#L171">171</span>
<span id="L172" rel="#L172">172</span>
<span id="L173" rel="#L173">173</span>
<span id="L174" rel="#L174">174</span>
<span id="L175" rel="#L175">175</span>
<span id="L176" rel="#L176">176</span>
<span id="L177" rel="#L177">177</span>
<span id="L178" rel="#L178">178</span>
<span id="L179" rel="#L179">179</span>
<span id="L180" rel="#L180">180</span>
<span id="L181" rel="#L181">181</span>
<span id="L182" rel="#L182">182</span>
<span id="L183" rel="#L183">183</span>
<span id="L184" rel="#L184">184</span>
<span id="L185" rel="#L185">185</span>
<span id="L186" rel="#L186">186</span>
<span id="L187" rel="#L187">187</span>
<span id="L188" rel="#L188">188</span>
<span id="L189" rel="#L189">189</span>
<span id="L190" rel="#L190">190</span>
<span id="L191" rel="#L191">191</span>
<span id="L192" rel="#L192">192</span>
<span id="L193" rel="#L193">193</span>
<span id="L194" rel="#L194">194</span>
<span id="L195" rel="#L195">195</span>
<span id="L196" rel="#L196">196</span>
<span id="L197" rel="#L197">197</span>
<span id="L198" rel="#L198">198</span>
<span id="L199" rel="#L199">199</span>
<span id="L200" rel="#L200">200</span>
<span id="L201" rel="#L201">201</span>
<span id="L202" rel="#L202">202</span>
<span id="L203" rel="#L203">203</span>
<span id="L204" rel="#L204">204</span>
<span id="L205" rel="#L205">205</span>
<span id="L206" rel="#L206">206</span>
<span id="L207" rel="#L207">207</span>
<span id="L208" rel="#L208">208</span>
<span id="L209" rel="#L209">209</span>
<span id="L210" rel="#L210">210</span>
<span id="L211" rel="#L211">211</span>
<span id="L212" rel="#L212">212</span>
<span id="L213" rel="#L213">213</span>
<span id="L214" rel="#L214">214</span>
<span id="L215" rel="#L215">215</span>
<span id="L216" rel="#L216">216</span>
<span id="L217" rel="#L217">217</span>
<span id="L218" rel="#L218">218</span>
<span id="L219" rel="#L219">219</span>
<span id="L220" rel="#L220">220</span>
<span id="L221" rel="#L221">221</span>
<span id="L222" rel="#L222">222</span>
<span id="L223" rel="#L223">223</span>
<span id="L224" rel="#L224">224</span>
<span id="L225" rel="#L225">225</span>
<span id="L226" rel="#L226">226</span>
<span id="L227" rel="#L227">227</span>
<span id="L228" rel="#L228">228</span>
<span id="L229" rel="#L229">229</span>
<span id="L230" rel="#L230">230</span>
<span id="L231" rel="#L231">231</span>
<span id="L232" rel="#L232">232</span>
<span id="L233" rel="#L233">233</span>
<span id="L234" rel="#L234">234</span>
<span id="L235" rel="#L235">235</span>
<span id="L236" rel="#L236">236</span>
<span id="L237" rel="#L237">237</span>
<span id="L238" rel="#L238">238</span>
<span id="L239" rel="#L239">239</span>
<span id="L240" rel="#L240">240</span>
<span id="L241" rel="#L241">241</span>
<span id="L242" rel="#L242">242</span>
<span id="L243" rel="#L243">243</span>
<span id="L244" rel="#L244">244</span>
<span id="L245" rel="#L245">245</span>
<span id="L246" rel="#L246">246</span>
<span id="L247" rel="#L247">247</span>
<span id="L248" rel="#L248">248</span>
<span id="L249" rel="#L249">249</span>
<span id="L250" rel="#L250">250</span>
<span id="L251" rel="#L251">251</span>
<span id="L252" rel="#L252">252</span>
<span id="L253" rel="#L253">253</span>
<span id="L254" rel="#L254">254</span>
<span id="L255" rel="#L255">255</span>
<span id="L256" rel="#L256">256</span>
<span id="L257" rel="#L257">257</span>
<span id="L258" rel="#L258">258</span>
<span id="L259" rel="#L259">259</span>
<span id="L260" rel="#L260">260</span>
<span id="L261" rel="#L261">261</span>
<span id="L262" rel="#L262">262</span>
<span id="L263" rel="#L263">263</span>
<span id="L264" rel="#L264">264</span>
<span id="L265" rel="#L265">265</span>
<span id="L266" rel="#L266">266</span>
<span id="L267" rel="#L267">267</span>
<span id="L268" rel="#L268">268</span>
<span id="L269" rel="#L269">269</span>
<span id="L270" rel="#L270">270</span>
<span id="L271" rel="#L271">271</span>
<span id="L272" rel="#L272">272</span>
<span id="L273" rel="#L273">273</span>
<span id="L274" rel="#L274">274</span>
<span id="L275" rel="#L275">275</span>
<span id="L276" rel="#L276">276</span>
<span id="L277" rel="#L277">277</span>
<span id="L278" rel="#L278">278</span>
<span id="L279" rel="#L279">279</span>
<span id="L280" rel="#L280">280</span>
<span id="L281" rel="#L281">281</span>
<span id="L282" rel="#L282">282</span>
<span id="L283" rel="#L283">283</span>
<span id="L284" rel="#L284">284</span>
<span id="L285" rel="#L285">285</span>
<span id="L286" rel="#L286">286</span>
<span id="L287" rel="#L287">287</span>
<span id="L288" rel="#L288">288</span>
<span id="L289" rel="#L289">289</span>
<span id="L290" rel="#L290">290</span>
<span id="L291" rel="#L291">291</span>
<span id="L292" rel="#L292">292</span>
<span id="L293" rel="#L293">293</span>
<span id="L294" rel="#L294">294</span>
<span id="L295" rel="#L295">295</span>
<span id="L296" rel="#L296">296</span>
<span id="L297" rel="#L297">297</span>
<span id="L298" rel="#L298">298</span>
<span id="L299" rel="#L299">299</span>
<span id="L300" rel="#L300">300</span>
<span id="L301" rel="#L301">301</span>
<span id="L302" rel="#L302">302</span>
<span id="L303" rel="#L303">303</span>
<span id="L304" rel="#L304">304</span>
<span id="L305" rel="#L305">305</span>
<span id="L306" rel="#L306">306</span>
<span id="L307" rel="#L307">307</span>
<span id="L308" rel="#L308">308</span>
<span id="L309" rel="#L309">309</span>
<span id="L310" rel="#L310">310</span>
<span id="L311" rel="#L311">311</span>
<span id="L312" rel="#L312">312</span>
<span id="L313" rel="#L313">313</span>
<span id="L314" rel="#L314">314</span>
<span id="L315" rel="#L315">315</span>
<span id="L316" rel="#L316">316</span>
<span id="L317" rel="#L317">317</span>
<span id="L318" rel="#L318">318</span>
<span id="L319" rel="#L319">319</span>
<span id="L320" rel="#L320">320</span>
<span id="L321" rel="#L321">321</span>
<span id="L322" rel="#L322">322</span>
<span id="L323" rel="#L323">323</span>
<span id="L324" rel="#L324">324</span>
<span id="L325" rel="#L325">325</span>
<span id="L326" rel="#L326">326</span>
<span id="L327" rel="#L327">327</span>
<span id="L328" rel="#L328">328</span>
<span id="L329" rel="#L329">329</span>
<span id="L330" rel="#L330">330</span>
<span id="L331" rel="#L331">331</span>
<span id="L332" rel="#L332">332</span>
<span id="L333" rel="#L333">333</span>
<span id="L334" rel="#L334">334</span>
<span id="L335" rel="#L335">335</span>
<span id="L336" rel="#L336">336</span>
<span id="L337" rel="#L337">337</span>
<span id="L338" rel="#L338">338</span>
<span id="L339" rel="#L339">339</span>
<span id="L340" rel="#L340">340</span>
<span id="L341" rel="#L341">341</span>
<span id="L342" rel="#L342">342</span>
<span id="L343" rel="#L343">343</span>
<span id="L344" rel="#L344">344</span>
<span id="L345" rel="#L345">345</span>
<span id="L346" rel="#L346">346</span>
<span id="L347" rel="#L347">347</span>
<span id="L348" rel="#L348">348</span>
<span id="L349" rel="#L349">349</span>
<span id="L350" rel="#L350">350</span>
<span id="L351" rel="#L351">351</span>
<span id="L352" rel="#L352">352</span>
<span id="L353" rel="#L353">353</span>
<span id="L354" rel="#L354">354</span>
<span id="L355" rel="#L355">355</span>
<span id="L356" rel="#L356">356</span>
<span id="L357" rel="#L357">357</span>
<span id="L358" rel="#L358">358</span>
<span id="L359" rel="#L359">359</span>
<span id="L360" rel="#L360">360</span>
<span id="L361" rel="#L361">361</span>
<span id="L362" rel="#L362">362</span>
<span id="L363" rel="#L363">363</span>
<span id="L364" rel="#L364">364</span>
<span id="L365" rel="#L365">365</span>
<span id="L366" rel="#L366">366</span>
<span id="L367" rel="#L367">367</span>
<span id="L368" rel="#L368">368</span>
<span id="L369" rel="#L369">369</span>
<span id="L370" rel="#L370">370</span>
<span id="L371" rel="#L371">371</span>
<span id="L372" rel="#L372">372</span>
<span id="L373" rel="#L373">373</span>
<span id="L374" rel="#L374">374</span>
<span id="L375" rel="#L375">375</span>
<span id="L376" rel="#L376">376</span>
<span id="L377" rel="#L377">377</span>
<span id="L378" rel="#L378">378</span>
<span id="L379" rel="#L379">379</span>
<span id="L380" rel="#L380">380</span>
<span id="L381" rel="#L381">381</span>
<span id="L382" rel="#L382">382</span>
<span id="L383" rel="#L383">383</span>
<span id="L384" rel="#L384">384</span>
<span id="L385" rel="#L385">385</span>
<span id="L386" rel="#L386">386</span>
<span id="L387" rel="#L387">387</span>
<span id="L388" rel="#L388">388</span>
<span id="L389" rel="#L389">389</span>
<span id="L390" rel="#L390">390</span>
<span id="L391" rel="#L391">391</span>
<span id="L392" rel="#L392">392</span>
<span id="L393" rel="#L393">393</span>
<span id="L394" rel="#L394">394</span>
<span id="L395" rel="#L395">395</span>
<span id="L396" rel="#L396">396</span>
<span id="L397" rel="#L397">397</span>
<span id="L398" rel="#L398">398</span>
<span id="L399" rel="#L399">399</span>
<span id="L400" rel="#L400">400</span>
<span id="L401" rel="#L401">401</span>
<span id="L402" rel="#L402">402</span>
<span id="L403" rel="#L403">403</span>
<span id="L404" rel="#L404">404</span>
<span id="L405" rel="#L405">405</span>
<span id="L406" rel="#L406">406</span>
<span id="L407" rel="#L407">407</span>
<span id="L408" rel="#L408">408</span>
<span id="L409" rel="#L409">409</span>
<span id="L410" rel="#L410">410</span>
<span id="L411" rel="#L411">411</span>
<span id="L412" rel="#L412">412</span>
<span id="L413" rel="#L413">413</span>
<span id="L414" rel="#L414">414</span>
<span id="L415" rel="#L415">415</span>
<span id="L416" rel="#L416">416</span>
<span id="L417" rel="#L417">417</span>
<span id="L418" rel="#L418">418</span>
<span id="L419" rel="#L419">419</span>
<span id="L420" rel="#L420">420</span>
<span id="L421" rel="#L421">421</span>
<span id="L422" rel="#L422">422</span>
<span id="L423" rel="#L423">423</span>
<span id="L424" rel="#L424">424</span>
<span id="L425" rel="#L425">425</span>
<span id="L426" rel="#L426">426</span>
<span id="L427" rel="#L427">427</span>
<span id="L428" rel="#L428">428</span>
<span id="L429" rel="#L429">429</span>
<span id="L430" rel="#L430">430</span>
<span id="L431" rel="#L431">431</span>
<span id="L432" rel="#L432">432</span>
<span id="L433" rel="#L433">433</span>
<span id="L434" rel="#L434">434</span>
<span id="L435" rel="#L435">435</span>
<span id="L436" rel="#L436">436</span>
<span id="L437" rel="#L437">437</span>
<span id="L438" rel="#L438">438</span>
<span id="L439" rel="#L439">439</span>
<span id="L440" rel="#L440">440</span>
<span id="L441" rel="#L441">441</span>
<span id="L442" rel="#L442">442</span>
<span id="L443" rel="#L443">443</span>
<span id="L444" rel="#L444">444</span>
<span id="L445" rel="#L445">445</span>
<span id="L446" rel="#L446">446</span>
<span id="L447" rel="#L447">447</span>
<span id="L448" rel="#L448">448</span>
<span id="L449" rel="#L449">449</span>
<span id="L450" rel="#L450">450</span>
<span id="L451" rel="#L451">451</span>
<span id="L452" rel="#L452">452</span>
<span id="L453" rel="#L453">453</span>
<span id="L454" rel="#L454">454</span>
<span id="L455" rel="#L455">455</span>
<span id="L456" rel="#L456">456</span>
<span id="L457" rel="#L457">457</span>
<span id="L458" rel="#L458">458</span>
<span id="L459" rel="#L459">459</span>
<span id="L460" rel="#L460">460</span>
<span id="L461" rel="#L461">461</span>
<span id="L462" rel="#L462">462</span>
<span id="L463" rel="#L463">463</span>
<span id="L464" rel="#L464">464</span>
<span id="L465" rel="#L465">465</span>
<span id="L466" rel="#L466">466</span>
<span id="L467" rel="#L467">467</span>
<span id="L468" rel="#L468">468</span>
<span id="L469" rel="#L469">469</span>
<span id="L470" rel="#L470">470</span>
<span id="L471" rel="#L471">471</span>
<span id="L472" rel="#L472">472</span>
<span id="L473" rel="#L473">473</span>
<span id="L474" rel="#L474">474</span>
<span id="L475" rel="#L475">475</span>
<span id="L476" rel="#L476">476</span>
<span id="L477" rel="#L477">477</span>
<span id="L478" rel="#L478">478</span>
<span id="L479" rel="#L479">479</span>
<span id="L480" rel="#L480">480</span>
<span id="L481" rel="#L481">481</span>
<span id="L482" rel="#L482">482</span>
<span id="L483" rel="#L483">483</span>
<span id="L484" rel="#L484">484</span>
<span id="L485" rel="#L485">485</span>
<span id="L486" rel="#L486">486</span>
<span id="L487" rel="#L487">487</span>
<span id="L488" rel="#L488">488</span>
<span id="L489" rel="#L489">489</span>
<span id="L490" rel="#L490">490</span>
<span id="L491" rel="#L491">491</span>
<span id="L492" rel="#L492">492</span>
<span id="L493" rel="#L493">493</span>
<span id="L494" rel="#L494">494</span>
<span id="L495" rel="#L495">495</span>
<span id="L496" rel="#L496">496</span>
<span id="L497" rel="#L497">497</span>
<span id="L498" rel="#L498">498</span>
<span id="L499" rel="#L499">499</span>
<span id="L500" rel="#L500">500</span>
<span id="L501" rel="#L501">501</span>
<span id="L502" rel="#L502">502</span>
<span id="L503" rel="#L503">503</span>
<span id="L504" rel="#L504">504</span>
<span id="L505" rel="#L505">505</span>
<span id="L506" rel="#L506">506</span>
<span id="L507" rel="#L507">507</span>
<span id="L508" rel="#L508">508</span>
<span id="L509" rel="#L509">509</span>
<span id="L510" rel="#L510">510</span>
<span id="L511" rel="#L511">511</span>
<span id="L512" rel="#L512">512</span>
<span id="L513" rel="#L513">513</span>
<span id="L514" rel="#L514">514</span>
<span id="L515" rel="#L515">515</span>
<span id="L516" rel="#L516">516</span>
<span id="L517" rel="#L517">517</span>
<span id="L518" rel="#L518">518</span>
<span id="L519" rel="#L519">519</span>
<span id="L520" rel="#L520">520</span>
<span id="L521" rel="#L521">521</span>
<span id="L522" rel="#L522">522</span>
<span id="L523" rel="#L523">523</span>
<span id="L524" rel="#L524">524</span>
<span id="L525" rel="#L525">525</span>
<span id="L526" rel="#L526">526</span>
<span id="L527" rel="#L527">527</span>
<span id="L528" rel="#L528">528</span>
<span id="L529" rel="#L529">529</span>
<span id="L530" rel="#L530">530</span>
<span id="L531" rel="#L531">531</span>
<span id="L532" rel="#L532">532</span>
<span id="L533" rel="#L533">533</span>
<span id="L534" rel="#L534">534</span>
<span id="L535" rel="#L535">535</span>
<span id="L536" rel="#L536">536</span>
<span id="L537" rel="#L537">537</span>
<span id="L538" rel="#L538">538</span>
<span id="L539" rel="#L539">539</span>
<span id="L540" rel="#L540">540</span>
<span id="L541" rel="#L541">541</span>
<span id="L542" rel="#L542">542</span>
<span id="L543" rel="#L543">543</span>
<span id="L544" rel="#L544">544</span>
<span id="L545" rel="#L545">545</span>
<span id="L546" rel="#L546">546</span>
<span id="L547" rel="#L547">547</span>
<span id="L548" rel="#L548">548</span>
<span id="L549" rel="#L549">549</span>
<span id="L550" rel="#L550">550</span>
<span id="L551" rel="#L551">551</span>
<span id="L552" rel="#L552">552</span>
<span id="L553" rel="#L553">553</span>
<span id="L554" rel="#L554">554</span>
<span id="L555" rel="#L555">555</span>
<span id="L556" rel="#L556">556</span>
<span id="L557" rel="#L557">557</span>
<span id="L558" rel="#L558">558</span>
<span id="L559" rel="#L559">559</span>
<span id="L560" rel="#L560">560</span>
<span id="L561" rel="#L561">561</span>
<span id="L562" rel="#L562">562</span>
<span id="L563" rel="#L563">563</span>
<span id="L564" rel="#L564">564</span>
<span id="L565" rel="#L565">565</span>
<span id="L566" rel="#L566">566</span>
<span id="L567" rel="#L567">567</span>
<span id="L568" rel="#L568">568</span>
<span id="L569" rel="#L569">569</span>
<span id="L570" rel="#L570">570</span>
<span id="L571" rel="#L571">571</span>
<span id="L572" rel="#L572">572</span>
<span id="L573" rel="#L573">573</span>
<span id="L574" rel="#L574">574</span>
<span id="L575" rel="#L575">575</span>
<span id="L576" rel="#L576">576</span>
<span id="L577" rel="#L577">577</span>
<span id="L578" rel="#L578">578</span>
<span id="L579" rel="#L579">579</span>
<span id="L580" rel="#L580">580</span>
<span id="L581" rel="#L581">581</span>
<span id="L582" rel="#L582">582</span>
<span id="L583" rel="#L583">583</span>
<span id="L584" rel="#L584">584</span>
<span id="L585" rel="#L585">585</span>
<span id="L586" rel="#L586">586</span>
<span id="L587" rel="#L587">587</span>
<span id="L588" rel="#L588">588</span>
<span id="L589" rel="#L589">589</span>
<span id="L590" rel="#L590">590</span>
<span id="L591" rel="#L591">591</span>
<span id="L592" rel="#L592">592</span>
<span id="L593" rel="#L593">593</span>
<span id="L594" rel="#L594">594</span>
<span id="L595" rel="#L595">595</span>
<span id="L596" rel="#L596">596</span>
<span id="L597" rel="#L597">597</span>
<span id="L598" rel="#L598">598</span>
<span id="L599" rel="#L599">599</span>
<span id="L600" rel="#L600">600</span>
<span id="L601" rel="#L601">601</span>
<span id="L602" rel="#L602">602</span>
<span id="L603" rel="#L603">603</span>
<span id="L604" rel="#L604">604</span>
<span id="L605" rel="#L605">605</span>
<span id="L606" rel="#L606">606</span>
<span id="L607" rel="#L607">607</span>
<span id="L608" rel="#L608">608</span>
<span id="L609" rel="#L609">609</span>
<span id="L610" rel="#L610">610</span>
<span id="L611" rel="#L611">611</span>
<span id="L612" rel="#L612">612</span>
<span id="L613" rel="#L613">613</span>
<span id="L614" rel="#L614">614</span>
<span id="L615" rel="#L615">615</span>
<span id="L616" rel="#L616">616</span>
<span id="L617" rel="#L617">617</span>
<span id="L618" rel="#L618">618</span>
<span id="L619" rel="#L619">619</span>
<span id="L620" rel="#L620">620</span>
<span id="L621" rel="#L621">621</span>
<span id="L622" rel="#L622">622</span>
<span id="L623" rel="#L623">623</span>
<span id="L624" rel="#L624">624</span>
<span id="L625" rel="#L625">625</span>
<span id="L626" rel="#L626">626</span>
<span id="L627" rel="#L627">627</span>
<span id="L628" rel="#L628">628</span>
<span id="L629" rel="#L629">629</span>
<span id="L630" rel="#L630">630</span>
<span id="L631" rel="#L631">631</span>
<span id="L632" rel="#L632">632</span>
<span id="L633" rel="#L633">633</span>
<span id="L634" rel="#L634">634</span>
<span id="L635" rel="#L635">635</span>
<span id="L636" rel="#L636">636</span>
<span id="L637" rel="#L637">637</span>
<span id="L638" rel="#L638">638</span>
<span id="L639" rel="#L639">639</span>
<span id="L640" rel="#L640">640</span>
<span id="L641" rel="#L641">641</span>
<span id="L642" rel="#L642">642</span>
<span id="L643" rel="#L643">643</span>
<span id="L644" rel="#L644">644</span>
<span id="L645" rel="#L645">645</span>
<span id="L646" rel="#L646">646</span>
<span id="L647" rel="#L647">647</span>
<span id="L648" rel="#L648">648</span>
<span id="L649" rel="#L649">649</span>
<span id="L650" rel="#L650">650</span>
<span id="L651" rel="#L651">651</span>
<span id="L652" rel="#L652">652</span>
<span id="L653" rel="#L653">653</span>
<span id="L654" rel="#L654">654</span>
<span id="L655" rel="#L655">655</span>
<span id="L656" rel="#L656">656</span>
<span id="L657" rel="#L657">657</span>
<span id="L658" rel="#L658">658</span>
<span id="L659" rel="#L659">659</span>
<span id="L660" rel="#L660">660</span>
<span id="L661" rel="#L661">661</span>
<span id="L662" rel="#L662">662</span>
<span id="L663" rel="#L663">663</span>
<span id="L664" rel="#L664">664</span>
<span id="L665" rel="#L665">665</span>
<span id="L666" rel="#L666">666</span>
<span id="L667" rel="#L667">667</span>
<span id="L668" rel="#L668">668</span>
<span id="L669" rel="#L669">669</span>
<span id="L670" rel="#L670">670</span>
<span id="L671" rel="#L671">671</span>
<span id="L672" rel="#L672">672</span>
<span id="L673" rel="#L673">673</span>
<span id="L674" rel="#L674">674</span>
<span id="L675" rel="#L675">675</span>
<span id="L676" rel="#L676">676</span>
<span id="L677" rel="#L677">677</span>
<span id="L678" rel="#L678">678</span>
<span id="L679" rel="#L679">679</span>
<span id="L680" rel="#L680">680</span>
<span id="L681" rel="#L681">681</span>
<span id="L682" rel="#L682">682</span>
<span id="L683" rel="#L683">683</span>
<span id="L684" rel="#L684">684</span>
<span id="L685" rel="#L685">685</span>
<span id="L686" rel="#L686">686</span>
<span id="L687" rel="#L687">687</span>
<span id="L688" rel="#L688">688</span>
<span id="L689" rel="#L689">689</span>
<span id="L690" rel="#L690">690</span>
<span id="L691" rel="#L691">691</span>
<span id="L692" rel="#L692">692</span>
<span id="L693" rel="#L693">693</span>
<span id="L694" rel="#L694">694</span>
<span id="L695" rel="#L695">695</span>
<span id="L696" rel="#L696">696</span>
<span id="L697" rel="#L697">697</span>
<span id="L698" rel="#L698">698</span>
<span id="L699" rel="#L699">699</span>
<span id="L700" rel="#L700">700</span>
<span id="L701" rel="#L701">701</span>
<span id="L702" rel="#L702">702</span>
<span id="L703" rel="#L703">703</span>
<span id="L704" rel="#L704">704</span>
<span id="L705" rel="#L705">705</span>
<span id="L706" rel="#L706">706</span>
<span id="L707" rel="#L707">707</span>
<span id="L708" rel="#L708">708</span>
<span id="L709" rel="#L709">709</span>
<span id="L710" rel="#L710">710</span>
<span id="L711" rel="#L711">711</span>
<span id="L712" rel="#L712">712</span>
<span id="L713" rel="#L713">713</span>
<span id="L714" rel="#L714">714</span>
<span id="L715" rel="#L715">715</span>
<span id="L716" rel="#L716">716</span>
<span id="L717" rel="#L717">717</span>
<span id="L718" rel="#L718">718</span>
<span id="L719" rel="#L719">719</span>
<span id="L720" rel="#L720">720</span>
<span id="L721" rel="#L721">721</span>
<span id="L722" rel="#L722">722</span>
<span id="L723" rel="#L723">723</span>
<span id="L724" rel="#L724">724</span>
<span id="L725" rel="#L725">725</span>
<span id="L726" rel="#L726">726</span>
<span id="L727" rel="#L727">727</span>
<span id="L728" rel="#L728">728</span>
<span id="L729" rel="#L729">729</span>
<span id="L730" rel="#L730">730</span>
<span id="L731" rel="#L731">731</span>
<span id="L732" rel="#L732">732</span>
<span id="L733" rel="#L733">733</span>
<span id="L734" rel="#L734">734</span>
<span id="L735" rel="#L735">735</span>
<span id="L736" rel="#L736">736</span>
<span id="L737" rel="#L737">737</span>
<span id="L738" rel="#L738">738</span>
<span id="L739" rel="#L739">739</span>
<span id="L740" rel="#L740">740</span>
<span id="L741" rel="#L741">741</span>
<span id="L742" rel="#L742">742</span>
<span id="L743" rel="#L743">743</span>
<span id="L744" rel="#L744">744</span>
<span id="L745" rel="#L745">745</span>
<span id="L746" rel="#L746">746</span>
<span id="L747" rel="#L747">747</span>
<span id="L748" rel="#L748">748</span>
<span id="L749" rel="#L749">749</span>
<span id="L750" rel="#L750">750</span>
<span id="L751" rel="#L751">751</span>
<span id="L752" rel="#L752">752</span>
<span id="L753" rel="#L753">753</span>
<span id="L754" rel="#L754">754</span>
<span id="L755" rel="#L755">755</span>
<span id="L756" rel="#L756">756</span>
<span id="L757" rel="#L757">757</span>
<span id="L758" rel="#L758">758</span>
<span id="L759" rel="#L759">759</span>
<span id="L760" rel="#L760">760</span>
<span id="L761" rel="#L761">761</span>
<span id="L762" rel="#L762">762</span>
<span id="L763" rel="#L763">763</span>
<span id="L764" rel="#L764">764</span>
<span id="L765" rel="#L765">765</span>
<span id="L766" rel="#L766">766</span>
<span id="L767" rel="#L767">767</span>
<span id="L768" rel="#L768">768</span>
<span id="L769" rel="#L769">769</span>
<span id="L770" rel="#L770">770</span>
<span id="L771" rel="#L771">771</span>
<span id="L772" rel="#L772">772</span>
<span id="L773" rel="#L773">773</span>
<span id="L774" rel="#L774">774</span>
<span id="L775" rel="#L775">775</span>
<span id="L776" rel="#L776">776</span>
<span id="L777" rel="#L777">777</span>
<span id="L778" rel="#L778">778</span>
<span id="L779" rel="#L779">779</span>
<span id="L780" rel="#L780">780</span>
<span id="L781" rel="#L781">781</span>
<span id="L782" rel="#L782">782</span>
<span id="L783" rel="#L783">783</span>
<span id="L784" rel="#L784">784</span>
<span id="L785" rel="#L785">785</span>
<span id="L786" rel="#L786">786</span>
<span id="L787" rel="#L787">787</span>
<span id="L788" rel="#L788">788</span>
<span id="L789" rel="#L789">789</span>
<span id="L790" rel="#L790">790</span>
<span id="L791" rel="#L791">791</span>
<span id="L792" rel="#L792">792</span>
<span id="L793" rel="#L793">793</span>
<span id="L794" rel="#L794">794</span>
<span id="L795" rel="#L795">795</span>
<span id="L796" rel="#L796">796</span>
<span id="L797" rel="#L797">797</span>
<span id="L798" rel="#L798">798</span>
<span id="L799" rel="#L799">799</span>
<span id="L800" rel="#L800">800</span>
<span id="L801" rel="#L801">801</span>
<span id="L802" rel="#L802">802</span>
<span id="L803" rel="#L803">803</span>
<span id="L804" rel="#L804">804</span>
<span id="L805" rel="#L805">805</span>
<span id="L806" rel="#L806">806</span>
<span id="L807" rel="#L807">807</span>
<span id="L808" rel="#L808">808</span>
<span id="L809" rel="#L809">809</span>
<span id="L810" rel="#L810">810</span>
<span id="L811" rel="#L811">811</span>
<span id="L812" rel="#L812">812</span>
<span id="L813" rel="#L813">813</span>
<span id="L814" rel="#L814">814</span>
<span id="L815" rel="#L815">815</span>
<span id="L816" rel="#L816">816</span>
<span id="L817" rel="#L817">817</span>
<span id="L818" rel="#L818">818</span>
<span id="L819" rel="#L819">819</span>
<span id="L820" rel="#L820">820</span>
<span id="L821" rel="#L821">821</span>
<span id="L822" rel="#L822">822</span>
<span id="L823" rel="#L823">823</span>
<span id="L824" rel="#L824">824</span>
<span id="L825" rel="#L825">825</span>
<span id="L826" rel="#L826">826</span>
<span id="L827" rel="#L827">827</span>
<span id="L828" rel="#L828">828</span>
<span id="L829" rel="#L829">829</span>
<span id="L830" rel="#L830">830</span>
<span id="L831" rel="#L831">831</span>
<span id="L832" rel="#L832">832</span>
<span id="L833" rel="#L833">833</span>
<span id="L834" rel="#L834">834</span>
<span id="L835" rel="#L835">835</span>
<span id="L836" rel="#L836">836</span>
<span id="L837" rel="#L837">837</span>
<span id="L838" rel="#L838">838</span>
<span id="L839" rel="#L839">839</span>
<span id="L840" rel="#L840">840</span>
<span id="L841" rel="#L841">841</span>
<span id="L842" rel="#L842">842</span>
<span id="L843" rel="#L843">843</span>
<span id="L844" rel="#L844">844</span>
<span id="L845" rel="#L845">845</span>
<span id="L846" rel="#L846">846</span>
<span id="L847" rel="#L847">847</span>
<span id="L848" rel="#L848">848</span>
<span id="L849" rel="#L849">849</span>
<span id="L850" rel="#L850">850</span>
<span id="L851" rel="#L851">851</span>
<span id="L852" rel="#L852">852</span>
<span id="L853" rel="#L853">853</span>
<span id="L854" rel="#L854">854</span>
<span id="L855" rel="#L855">855</span>
<span id="L856" rel="#L856">856</span>
<span id="L857" rel="#L857">857</span>
<span id="L858" rel="#L858">858</span>
<span id="L859" rel="#L859">859</span>
<span id="L860" rel="#L860">860</span>
<span id="L861" rel="#L861">861</span>
<span id="L862" rel="#L862">862</span>
<span id="L863" rel="#L863">863</span>
<span id="L864" rel="#L864">864</span>
<span id="L865" rel="#L865">865</span>
<span id="L866" rel="#L866">866</span>
<span id="L867" rel="#L867">867</span>
<span id="L868" rel="#L868">868</span>
<span id="L869" rel="#L869">869</span>
<span id="L870" rel="#L870">870</span>
<span id="L871" rel="#L871">871</span>
<span id="L872" rel="#L872">872</span>
<span id="L873" rel="#L873">873</span>
<span id="L874" rel="#L874">874</span>
<span id="L875" rel="#L875">875</span>
<span id="L876" rel="#L876">876</span>
<span id="L877" rel="#L877">877</span>
<span id="L878" rel="#L878">878</span>
<span id="L879" rel="#L879">879</span>
<span id="L880" rel="#L880">880</span>
<span id="L881" rel="#L881">881</span>
<span id="L882" rel="#L882">882</span>
<span id="L883" rel="#L883">883</span>
<span id="L884" rel="#L884">884</span>
<span id="L885" rel="#L885">885</span>
<span id="L886" rel="#L886">886</span>
<span id="L887" rel="#L887">887</span>
<span id="L888" rel="#L888">888</span>
<span id="L889" rel="#L889">889</span>
<span id="L890" rel="#L890">890</span>
<span id="L891" rel="#L891">891</span>
<span id="L892" rel="#L892">892</span>
<span id="L893" rel="#L893">893</span>
<span id="L894" rel="#L894">894</span>
<span id="L895" rel="#L895">895</span>
<span id="L896" rel="#L896">896</span>
<span id="L897" rel="#L897">897</span>
<span id="L898" rel="#L898">898</span>
<span id="L899" rel="#L899">899</span>
<span id="L900" rel="#L900">900</span>
<span id="L901" rel="#L901">901</span>
<span id="L902" rel="#L902">902</span>
<span id="L903" rel="#L903">903</span>
<span id="L904" rel="#L904">904</span>
<span id="L905" rel="#L905">905</span>
<span id="L906" rel="#L906">906</span>
<span id="L907" rel="#L907">907</span>
<span id="L908" rel="#L908">908</span>
<span id="L909" rel="#L909">909</span>
<span id="L910" rel="#L910">910</span>
<span id="L911" rel="#L911">911</span>
<span id="L912" rel="#L912">912</span>
<span id="L913" rel="#L913">913</span>
<span id="L914" rel="#L914">914</span>
<span id="L915" rel="#L915">915</span>
<span id="L916" rel="#L916">916</span>
<span id="L917" rel="#L917">917</span>
<span id="L918" rel="#L918">918</span>
<span id="L919" rel="#L919">919</span>
<span id="L920" rel="#L920">920</span>
<span id="L921" rel="#L921">921</span>
<span id="L922" rel="#L922">922</span>
<span id="L923" rel="#L923">923</span>
<span id="L924" rel="#L924">924</span>
<span id="L925" rel="#L925">925</span>
<span id="L926" rel="#L926">926</span>
<span id="L927" rel="#L927">927</span>
<span id="L928" rel="#L928">928</span>
<span id="L929" rel="#L929">929</span>
<span id="L930" rel="#L930">930</span>
<span id="L931" rel="#L931">931</span>
<span id="L932" rel="#L932">932</span>
<span id="L933" rel="#L933">933</span>
<span id="L934" rel="#L934">934</span>
<span id="L935" rel="#L935">935</span>
<span id="L936" rel="#L936">936</span>
<span id="L937" rel="#L937">937</span>
<span id="L938" rel="#L938">938</span>
<span id="L939" rel="#L939">939</span>
<span id="L940" rel="#L940">940</span>
<span id="L941" rel="#L941">941</span>
<span id="L942" rel="#L942">942</span>
<span id="L943" rel="#L943">943</span>
<span id="L944" rel="#L944">944</span>
<span id="L945" rel="#L945">945</span>
<span id="L946" rel="#L946">946</span>
<span id="L947" rel="#L947">947</span>
<span id="L948" rel="#L948">948</span>
<span id="L949" rel="#L949">949</span>
<span id="L950" rel="#L950">950</span>
<span id="L951" rel="#L951">951</span>
<span id="L952" rel="#L952">952</span>
<span id="L953" rel="#L953">953</span>
<span id="L954" rel="#L954">954</span>
<span id="L955" rel="#L955">955</span>
<span id="L956" rel="#L956">956</span>
<span id="L957" rel="#L957">957</span>
<span id="L958" rel="#L958">958</span>
<span id="L959" rel="#L959">959</span>
<span id="L960" rel="#L960">960</span>
<span id="L961" rel="#L961">961</span>
<span id="L962" rel="#L962">962</span>
<span id="L963" rel="#L963">963</span>
<span id="L964" rel="#L964">964</span>
<span id="L965" rel="#L965">965</span>
<span id="L966" rel="#L966">966</span>
<span id="L967" rel="#L967">967</span>
<span id="L968" rel="#L968">968</span>
<span id="L969" rel="#L969">969</span>
<span id="L970" rel="#L970">970</span>
<span id="L971" rel="#L971">971</span>
<span id="L972" rel="#L972">972</span>
<span id="L973" rel="#L973">973</span>
<span id="L974" rel="#L974">974</span>
<span id="L975" rel="#L975">975</span>
<span id="L976" rel="#L976">976</span>
<span id="L977" rel="#L977">977</span>
<span id="L978" rel="#L978">978</span>
<span id="L979" rel="#L979">979</span>
<span id="L980" rel="#L980">980</span>
<span id="L981" rel="#L981">981</span>
<span id="L982" rel="#L982">982</span>
<span id="L983" rel="#L983">983</span>
<span id="L984" rel="#L984">984</span>
<span id="L985" rel="#L985">985</span>
<span id="L986" rel="#L986">986</span>
<span id="L987" rel="#L987">987</span>
<span id="L988" rel="#L988">988</span>
<span id="L989" rel="#L989">989</span>
<span id="L990" rel="#L990">990</span>
<span id="L991" rel="#L991">991</span>
<span id="L992" rel="#L992">992</span>
<span id="L993" rel="#L993">993</span>
<span id="L994" rel="#L994">994</span>
<span id="L995" rel="#L995">995</span>
<span id="L996" rel="#L996">996</span>
<span id="L997" rel="#L997">997</span>
<span id="L998" rel="#L998">998</span>
<span id="L999" rel="#L999">999</span>
<span id="L1000" rel="#L1000">1000</span>
<span id="L1001" rel="#L1001">1001</span>
<span id="L1002" rel="#L1002">1002</span>
<span id="L1003" rel="#L1003">1003</span>
<span id="L1004" rel="#L1004">1004</span>
<span id="L1005" rel="#L1005">1005</span>
<span id="L1006" rel="#L1006">1006</span>
<span id="L1007" rel="#L1007">1007</span>
<span id="L1008" rel="#L1008">1008</span>
<span id="L1009" rel="#L1009">1009</span>
<span id="L1010" rel="#L1010">1010</span>
<span id="L1011" rel="#L1011">1011</span>
<span id="L1012" rel="#L1012">1012</span>
<span id="L1013" rel="#L1013">1013</span>
<span id="L1014" rel="#L1014">1014</span>
<span id="L1015" rel="#L1015">1015</span>
<span id="L1016" rel="#L1016">1016</span>
<span id="L1017" rel="#L1017">1017</span>
<span id="L1018" rel="#L1018">1018</span>
<span id="L1019" rel="#L1019">1019</span>
<span id="L1020" rel="#L1020">1020</span>
<span id="L1021" rel="#L1021">1021</span>
<span id="L1022" rel="#L1022">1022</span>
<span id="L1023" rel="#L1023">1023</span>
<span id="L1024" rel="#L1024">1024</span>
<span id="L1025" rel="#L1025">1025</span>
<span id="L1026" rel="#L1026">1026</span>
<span id="L1027" rel="#L1027">1027</span>
<span id="L1028" rel="#L1028">1028</span>
<span id="L1029" rel="#L1029">1029</span>
<span id="L1030" rel="#L1030">1030</span>
<span id="L1031" rel="#L1031">1031</span>
<span id="L1032" rel="#L1032">1032</span>
<span id="L1033" rel="#L1033">1033</span>
<span id="L1034" rel="#L1034">1034</span>
<span id="L1035" rel="#L1035">1035</span>
<span id="L1036" rel="#L1036">1036</span>
<span id="L1037" rel="#L1037">1037</span>
<span id="L1038" rel="#L1038">1038</span>
<span id="L1039" rel="#L1039">1039</span>
<span id="L1040" rel="#L1040">1040</span>
<span id="L1041" rel="#L1041">1041</span>
<span id="L1042" rel="#L1042">1042</span>
<span id="L1043" rel="#L1043">1043</span>
<span id="L1044" rel="#L1044">1044</span>
<span id="L1045" rel="#L1045">1045</span>
<span id="L1046" rel="#L1046">1046</span>
<span id="L1047" rel="#L1047">1047</span>
<span id="L1048" rel="#L1048">1048</span>
<span id="L1049" rel="#L1049">1049</span>
<span id="L1050" rel="#L1050">1050</span>
<span id="L1051" rel="#L1051">1051</span>
<span id="L1052" rel="#L1052">1052</span>
<span id="L1053" rel="#L1053">1053</span>
<span id="L1054" rel="#L1054">1054</span>
<span id="L1055" rel="#L1055">1055</span>
<span id="L1056" rel="#L1056">1056</span>
<span id="L1057" rel="#L1057">1057</span>
<span id="L1058" rel="#L1058">1058</span>
<span id="L1059" rel="#L1059">1059</span>
<span id="L1060" rel="#L1060">1060</span>
<span id="L1061" rel="#L1061">1061</span>
<span id="L1062" rel="#L1062">1062</span>
<span id="L1063" rel="#L1063">1063</span>
<span id="L1064" rel="#L1064">1064</span>

          </td>
          <td class="blob-line-code">
                  <div class="highlight"><pre><div class='line' id='LC1'><span class="o">&lt;?</span><span class="nx">php</span> </div><div class='line' id='LC2'><br/></div><div class='line' id='LC3'><span class="sd">/**</span></div><div class='line' id='LC4'><span class="sd"> * Handles site-wide logins, sessions and self-registering</span></div><div class='line' id='LC5'><span class="sd"> *</span></div><div class='line' id='LC6'><span class="sd"> * @package mck_login</span></div><div class='line' id='LC7'><span class="sd"> * @author Casalegno Marco &lt;http://www.kreatore.it/&gt;</span></div><div class='line' id='LC8'><span class="sd"> * @author Jukka Svahn &lt;http://rahforum.biz&gt;</span></div><div class='line' id='LC9'><span class="sd"> * @license GNU GPLv2</span></div><div class='line' id='LC10'><span class="sd"> * @link http://www.kreatore.it/txp/mck_login</span></div><div class='line' id='LC11'><span class="sd"> * @link https://github.com/gocom/mck_login</span></div><div class='line' id='LC12'><span class="sd"> *</span></div><div class='line' id='LC13'><span class="sd"> * Requires Textpattern v4.4.1 (or newer) and PHP v5.2 (or newer)</span></div><div class='line' id='LC14'><span class="sd"> */</span></div><div class='line' id='LC15'><br/></div><div class='line' id='LC16'>	<span class="k">if</span><span class="p">(</span><span class="o">@</span><span class="nx">txpinterface</span> <span class="o">==</span> <span class="s1">&#39;public&#39;</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC17'>		<span class="nx">register_callback</span><span class="p">(</span><span class="k">array</span><span class="p">(</span><span class="s1">&#39;mck_login&#39;</span><span class="p">,</span> <span class="s1">&#39;handler&#39;</span><span class="p">),</span> <span class="s1">&#39;textpattern&#39;</span><span class="p">);</span></div><div class='line' id='LC18'>	<span class="p">}</span></div><div class='line' id='LC19'><br/></div><div class='line' id='LC20'>	<span class="k">elseif</span><span class="p">(</span><span class="o">@</span><span class="nx">txpinterface</span> <span class="o">==</span> <span class="s1">&#39;admin&#39;</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC21'>		<span class="nx">register_callback</span><span class="p">(</span><span class="k">array</span><span class="p">(</span><span class="s1">&#39;mck_login&#39;</span><span class="p">,</span> <span class="s1">&#39;uninstall&#39;</span><span class="p">),</span> <span class="s1">&#39;plugin_lifecycle.mck_login&#39;</span><span class="p">,</span> <span class="s1">&#39;deleted&#39;</span><span class="p">);</span></div><div class='line' id='LC22'>	<span class="p">}</span></div><div class='line' id='LC23'><br/></div><div class='line' id='LC24'><span class="sd">/**</span></div><div class='line' id='LC25'><span class="sd"> * Handles form validation and saving, all of the non-tag stuff</span></div><div class='line' id='LC26'><span class="sd"> */</span></div><div class='line' id='LC27'><br/></div><div class='line' id='LC28'><span class="k">class</span> <span class="nc">mck_login</span> <span class="p">{</span></div><div class='line' id='LC29'><br/></div><div class='line' id='LC30'>	<span class="k">static</span> <span class="k">public</span> <span class="nv">$form_errors</span> <span class="o">=</span> <span class="k">array</span><span class="p">();</span></div><div class='line' id='LC31'>	<span class="k">static</span> <span class="k">public</span> <span class="nv">$action</span><span class="p">;</span></div><div class='line' id='LC32'><br/></div><div class='line' id='LC33'>	<span class="sd">/**</span></div><div class='line' id='LC34'><span class="sd">	 * Uninstalls the plugin</span></div><div class='line' id='LC35'><span class="sd">	 * @return nothing</span></div><div class='line' id='LC36'><span class="sd">	 * @access private</span></div><div class='line' id='LC37'><span class="sd">	 */</span></div><div class='line' id='LC38'><br/></div><div class='line' id='LC39'>	<span class="k">static</span> <span class="k">public</span> <span class="k">function</span> <span class="nf">uninstall</span><span class="p">()</span> <span class="p">{</span></div><div class='line' id='LC40'>		<span class="nx">safe_delete</span><span class="p">(</span><span class="s1">&#39;txp_lang&#39;</span><span class="p">,</span> <span class="s2">&quot;name LIKE &#39;mck\_login\_&#39;&quot;</span><span class="p">);</span></div><div class='line' id='LC41'>	<span class="p">}</span></div><div class='line' id='LC42'><br/></div><div class='line' id='LC43'>	<span class="sd">/**</span></div><div class='line' id='LC44'><span class="sd">	 * Add and get form validation errors</span></div><div class='line' id='LC45'><span class="sd">	 * @param string $message Either l10n string, or single line of text</span></div><div class='line' id='LC46'><span class="sd">	 * @param string $type For which form the error is for.</span></div><div class='line' id='LC47'><span class="sd">	 * @return array</span></div><div class='line' id='LC48'><span class="sd">	 * &lt;code&gt;</span></div><div class='line' id='LC49'><span class="sd">	 *		mck_login::error(&#39;abc_l10n_string&#39;);</span></div><div class='line' id='LC50'><span class="sd">	 * &lt;/code&gt;</span></div><div class='line' id='LC51'><span class="sd">	 */</span></div><div class='line' id='LC52'><br/></div><div class='line' id='LC53'>	<span class="k">static</span> <span class="k">public</span> <span class="k">function</span> <span class="nf">error</span><span class="p">(</span><span class="nv">$message</span><span class="o">=</span><span class="k">NULL</span><span class="p">,</span> <span class="nv">$type</span><span class="o">=</span><span class="k">NULL</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC54'><br/></div><div class='line' id='LC55'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$type</span><span class="p">)</span></div><div class='line' id='LC56'>			<span class="nv">$type</span> <span class="o">=</span> <span class="nx">self</span><span class="o">::</span><span class="nv">$action</span><span class="p">;</span></div><div class='line' id='LC57'><br/></div><div class='line' id='LC58'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nb">isset</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="nv">$form_errors</span><span class="p">[</span><span class="nv">$type</span><span class="p">]))</span></div><div class='line' id='LC59'>			<span class="nx">self</span><span class="o">::</span><span class="nv">$form_errors</span><span class="p">[</span><span class="nv">$type</span><span class="p">]</span> <span class="o">=</span> <span class="k">array</span><span class="p">();</span></div><div class='line' id='LC60'><br/></div><div class='line' id='LC61'>		<span class="k">if</span><span class="p">(</span><span class="nv">$message</span> <span class="o">!==</span> <span class="k">NULL</span><span class="p">)</span></div><div class='line' id='LC62'>			<span class="nx">self</span><span class="o">::</span><span class="nv">$form_errors</span><span class="p">[</span><span class="nv">$type</span><span class="p">][]</span> <span class="o">=</span> <span class="nv">$message</span><span class="p">;</span></div><div class='line' id='LC63'><br/></div><div class='line' id='LC64'>		<span class="k">return</span> <span class="nx">self</span><span class="o">::</span><span class="nv">$form_errors</span><span class="p">[</span><span class="nv">$type</span><span class="p">];</span></div><div class='line' id='LC65'>	<span class="p">}</span></div><div class='line' id='LC66'><br/></div><div class='line' id='LC67'>	<span class="sd">/**</span></div><div class='line' id='LC68'><span class="sd">	 * Validates login details and handles sessions</span></div><div class='line' id='LC69'><span class="sd">	 * @return nothing</span></div><div class='line' id='LC70'><span class="sd">	 * @see txp_validate(), generate_password(), $sitename</span></div><div class='line' id='LC71'><span class="sd">	 * @access private</span></div><div class='line' id='LC72'><span class="sd">	 */</span></div><div class='line' id='LC73'><br/></div><div class='line' id='LC74'>	<span class="k">static</span> <span class="k">public</span> <span class="k">function</span> <span class="nf">handler</span><span class="p">()</span> <span class="p">{</span></div><div class='line' id='LC75'><br/></div><div class='line' id='LC76'>		<span class="k">global</span> <span class="nv">$sitename</span><span class="p">;</span></div><div class='line' id='LC77'><br/></div><div class='line' id='LC78'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">doArray</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC79'>			<span class="s1">&#39;name&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_login_name&#39;</span><span class="p">),</span></div><div class='line' id='LC80'>			<span class="s1">&#39;pass&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_login_pass&#39;</span><span class="p">),</span></div><div class='line' id='LC81'>			<span class="s1">&#39;stay&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_login_stay&#39;</span><span class="p">),</span></div><div class='line' id='LC82'>			<span class="s1">&#39;form&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_login_form&#39;</span><span class="p">),</span></div><div class='line' id='LC83'>			<span class="s1">&#39;reset&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_reset&#39;</span><span class="p">),</span></div><div class='line' id='LC84'>			<span class="s1">&#39;logout&#39;</span> <span class="o">=&gt;</span> <span class="nx">gps</span><span class="p">(</span><span class="s1">&#39;mck_logout&#39;</span><span class="p">),</span></div><div class='line' id='LC85'>		<span class="p">),</span> <span class="s1">&#39;trim&#39;</span><span class="p">));</span></div><div class='line' id='LC86'><br/></div><div class='line' id='LC87'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$form</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nv">$reset</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nv">$logout</span><span class="p">)</span></div><div class='line' id='LC88'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC89'><br/></div><div class='line' id='LC90'>		<span class="nv">$is_logged_in</span> <span class="o">=</span> <span class="nx">is_logged_in</span><span class="p">();</span></div><div class='line' id='LC91'><br/></div><div class='line' id='LC92'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nb">defined</span><span class="p">(</span><span class="s1">&#39;mck_login_pub_path&#39;</span><span class="p">))</span></div><div class='line' id='LC93'>			<span class="nb">define</span><span class="p">(</span><span class="s1">&#39;mck_login_pub_path&#39;</span><span class="p">,</span> <span class="nb">preg_replace</span><span class="p">(</span><span class="s1">&#39;|//$|&#39;</span><span class="p">,</span><span class="s1">&#39;/&#39;</span><span class="p">,</span> <span class="nx">rhu</span><span class="o">.</span><span class="s1">&#39;/&#39;</span><span class="p">));</span></div><div class='line' id='LC94'><br/></div><div class='line' id='LC95'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nb">defined</span><span class="p">(</span><span class="s1">&#39;mck_login_admin_path&#39;</span><span class="p">))</span></div><div class='line' id='LC96'>			<span class="nb">define</span><span class="p">(</span><span class="s1">&#39;mck_login_admin_path&#39;</span><span class="p">,</span> <span class="s1">&#39;/textpattern/&#39;</span><span class="p">);</span></div><div class='line' id='LC97'><br/></div><div class='line' id='LC98'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nb">defined</span><span class="p">(</span><span class="s1">&#39;mck_login_admin_domain&#39;</span><span class="p">))</span></div><div class='line' id='LC99'>			<span class="nb">define</span><span class="p">(</span><span class="s1">&#39;mck_login_admin_domain&#39;</span><span class="p">,</span> <span class="s1">&#39;&#39;</span><span class="p">);</span></div><div class='line' id='LC100'><br/></div><div class='line' id='LC101'>		<span class="cm">/*</span></div><div class='line' id='LC102'><span class="cm">			Confirm password reset request</span></div><div class='line' id='LC103'><span class="cm">		*/</span></div><div class='line' id='LC104'><br/></div><div class='line' id='LC105'>		<span class="k">if</span><span class="p">(</span><span class="nv">$reset</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nv">$form</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nv">$is_logged_in</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC106'><br/></div><div class='line' id='LC107'>			<span class="nx">self</span><span class="o">::</span><span class="nv">$action</span> <span class="o">=</span> <span class="s1">&#39;reset&#39;</span><span class="p">;</span></div><div class='line' id='LC108'><br/></div><div class='line' id='LC109'>			<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.reset_confirm&#39;</span><span class="p">);</span></div><div class='line' id='LC110'><br/></div><div class='line' id='LC111'>			<span class="nb">sleep</span><span class="p">(</span><span class="mi">3</span><span class="p">);</span></div><div class='line' id='LC112'><br/></div><div class='line' id='LC113'>			<span class="nv">$confirm</span> <span class="o">=</span> <span class="nb">pack</span><span class="p">(</span><span class="s1">&#39;H*&#39;</span><span class="p">,</span> <span class="nv">$reset</span><span class="p">);</span></div><div class='line' id='LC114'>			<span class="nv">$reset</span> <span class="o">=</span> <span class="nx">substr</span><span class="p">(</span><span class="nv">$confirm</span><span class="p">,</span> <span class="mi">5</span><span class="p">);</span></div><div class='line' id='LC115'><br/></div><div class='line' id='LC116'>			<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nb">strpos</span><span class="p">(</span><span class="nv">$reset</span><span class="p">,</span> <span class="s1">&#39;;&#39;</span><span class="p">))</span> <span class="p">{</span></div><div class='line' id='LC117'>				<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_token&#39;</span><span class="p">);</span></div><div class='line' id='LC118'>				<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC119'>			<span class="p">}</span></div><div class='line' id='LC120'><br/></div><div class='line' id='LC121'>			<span class="nv">$name</span> <span class="o">=</span> <span class="nb">explode</span><span class="p">(</span><span class="s1">&#39;;&#39;</span><span class="p">,</span> <span class="nv">$reset</span><span class="p">);</span></div><div class='line' id='LC122'>			<span class="nv">$redirect</span> <span class="o">=</span> <span class="nb">array_pop</span><span class="p">(</span><span class="nv">$name</span><span class="p">);</span></div><div class='line' id='LC123'>			<span class="nv">$name</span> <span class="o">=</span> <span class="nb">implode</span><span class="p">(</span><span class="s1">&#39;;&#39;</span><span class="p">,</span> <span class="nv">$name</span><span class="p">);</span></div><div class='line' id='LC124'><br/></div><div class='line' id='LC125'>			<span class="nv">$r</span> <span class="o">=</span> </div><div class='line' id='LC126'>				<span class="nx">safe_row</span><span class="p">(</span></div><div class='line' id='LC127'>					<span class="s1">&#39;nonce, email&#39;</span><span class="p">,</span></div><div class='line' id='LC128'>					<span class="s1">&#39;txp_users&#39;</span><span class="p">,</span></div><div class='line' id='LC129'>					<span class="s2">&quot;name=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span></div><div class='line' id='LC130'>				<span class="p">);</span></div><div class='line' id='LC131'><br/></div><div class='line' id='LC132'>			<span class="nv">$packed</span> <span class="o">=</span> <span class="nb">pack</span><span class="p">(</span><span class="s1">&#39;H*&#39;</span><span class="p">,</span> <span class="nx">substr</span><span class="p">(</span><span class="nb">md5</span><span class="p">(</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;nonce&#39;</span><span class="p">]</span> <span class="o">.</span> <span class="nv">$redirect</span><span class="p">),</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">10</span><span class="p">))</span> <span class="o">.</span> <span class="nv">$name</span> <span class="o">.</span> <span class="s1">&#39;;&#39;</span> <span class="o">.</span> <span class="nv">$redirect</span><span class="p">;</span></div><div class='line' id='LC133'><br/></div><div class='line' id='LC134'>			<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$r</span> <span class="o">||</span> <span class="o">!</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;nonce&#39;</span><span class="p">]</span> <span class="o">||</span> <span class="nv">$confirm</span> <span class="o">!==</span> <span class="nv">$packed</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC135'>				<span class="nb">sleep</span><span class="p">(</span><span class="mi">3</span><span class="p">);</span></div><div class='line' id='LC136'>				<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_token&#39;</span><span class="p">);</span></div><div class='line' id='LC137'>				<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC138'>			<span class="p">}</span></div><div class='line' id='LC139'><br/></div><div class='line' id='LC140'>			<span class="k">include_once</span> <span class="nx">txpath</span> <span class="o">.</span> <span class="s1">&#39;/lib/txplib_admin.php&#39;</span><span class="p">;</span></div><div class='line' id='LC141'>			<span class="k">include_once</span> <span class="nx">txpath</span> <span class="o">.</span> <span class="s1">&#39;/include/txp_auth.php&#39;</span><span class="p">;</span></div><div class='line' id='LC142'><br/></div><div class='line' id='LC143'>			<span class="nv">$pass</span> <span class="o">=</span> <span class="nx">generate_password</span><span class="p">(</span><span class="mi">12</span><span class="p">);</span></div><div class='line' id='LC144'>			<span class="nv">$hash</span> <span class="o">=</span> <span class="nx">txp_hash_password</span><span class="p">(</span><span class="nv">$pass</span><span class="p">);</span></div><div class='line' id='LC145'><br/></div><div class='line' id='LC146'>			<span class="k">if</span><span class="p">(</span></div><div class='line' id='LC147'>				<span class="nx">safe_update</span><span class="p">(</span></div><div class='line' id='LC148'>					<span class="s1">&#39;txp_users&#39;</span><span class="p">,</span></div><div class='line' id='LC149'>					<span class="s2">&quot;pass=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$hash</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;,</span></div><div class='line' id='LC150'><span class="s2">					nonce=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nb">md5</span><span class="p">(</span><span class="nv">$name</span><span class="o">.</span><span class="nb">pack</span><span class="p">(</span><span class="s1">&#39;H*&#39;</span><span class="p">,</span> <span class="nb">md5</span><span class="p">(</span><span class="nb">uniqid</span><span class="p">(</span><span class="nx">mt_rand</span><span class="p">(),</span> <span class="k">true</span><span class="p">)))))</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span><span class="p">,</span></div><div class='line' id='LC151'>					<span class="s2">&quot;name=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span></div><div class='line' id='LC152'>				<span class="p">)</span> <span class="o">===</span> <span class="k">false</span></div><div class='line' id='LC153'>			<span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC154'><br/></div><div class='line' id='LC155'>				<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;saving_failed&#39;</span><span class="p">);</span></div><div class='line' id='LC156'>				<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC157'>			<span class="p">}</span></div><div class='line' id='LC158'><br/></div><div class='line' id='LC159'>			<span class="nv">$message</span> <span class="o">=</span> </div><div class='line' id='LC160'>				<span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;greeting&#39;</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39; &#39;</span><span class="o">.</span><span class="nv">$name</span><span class="o">.</span><span class="s1">&#39;,&#39;</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC161'>				<span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;your_password_is&#39;</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;: &#39;</span><span class="o">.</span><span class="nv">$password</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC162'>				<span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;log_in_at&#39;</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;: &#39;</span><span class="o">.</span><span class="nx">hu</span><span class="o">.</span><span class="nv">$redirect</span><span class="p">;</span></div><div class='line' id='LC163'><br/></div><div class='line' id='LC164'>			<span class="nv">$subject</span> <span class="o">=</span> </div><div class='line' id='LC165'>				<span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;mck_login_your_new_password&#39;</span><span class="p">,</span> </div><div class='line' id='LC166'>					<span class="k">array</span><span class="p">(</span><span class="s1">&#39;{sitename}&#39;</span> <span class="o">=&gt;</span> <span class="nv">$sitename</span><span class="p">)</span></div><div class='line' id='LC167'>				<span class="p">);</span></div><div class='line' id='LC168'><br/></div><div class='line' id='LC169'>			<span class="k">if</span><span class="p">(</span><span class="nx">txpMail</span><span class="p">(</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;email&#39;</span><span class="p">],</span> <span class="nv">$subject</span><span class="p">,</span> <span class="nv">$message</span><span class="p">)</span> <span class="o">===</span> <span class="k">false</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC170'>				<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;could_not_mail&#39;</span><span class="p">);</span></div><div class='line' id='LC171'>				<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC172'>			<span class="p">}</span></div><div class='line' id='LC173'><br/></div><div class='line' id='LC174'>			<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.reset_confirmed&#39;</span><span class="p">);</span></div><div class='line' id='LC175'><br/></div><div class='line' id='LC176'>			<span class="nx">header</span><span class="p">(</span><span class="s1">&#39;Location: &#39;</span> <span class="o">.</span><span class="nx">hu</span><span class="o">.</span><span class="nv">$redirect</span><span class="p">);</span></div><div class='line' id='LC177'><br/></div><div class='line' id='LC178'>			<span class="nv">$msg</span> <span class="o">=</span> </div><div class='line' id='LC179'>				<span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;mck_login_redirect_message&#39;</span><span class="p">,</span> </div><div class='line' id='LC180'>					<span class="k">array</span><span class="p">(</span><span class="s1">&#39;{url}&#39;</span> <span class="o">=&gt;</span> <span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nx">hu</span><span class="o">.</span><span class="nv">$redirect</span><span class="p">))</span></div><div class='line' id='LC181'>				<span class="p">);</span></div><div class='line' id='LC182'><br/></div><div class='line' id='LC183'>			<span class="k">die</span><span class="p">(</span><span class="nv">$msg</span><span class="p">);</span></div><div class='line' id='LC184'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC185'>		<span class="p">}</span></div><div class='line' id='LC186'><br/></div><div class='line' id='LC187'>		<span class="cm">/*</span></div><div class='line' id='LC188'><span class="cm">			Log out</span></div><div class='line' id='LC189'><span class="cm">		*/</span></div><div class='line' id='LC190'><br/></div><div class='line' id='LC191'>		<span class="k">if</span><span class="p">(</span><span class="nv">$logout</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nv">$form</span> <span class="o">&amp;&amp;</span> <span class="nv">$is_logged_in</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC192'><br/></div><div class='line' id='LC193'>			<span class="nx">self</span><span class="o">::</span><span class="nv">$action</span> <span class="o">=</span> <span class="s1">&#39;logout&#39;</span><span class="p">;</span></div><div class='line' id='LC194'><br/></div><div class='line' id='LC195'>			<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.logout&#39;</span><span class="p">);</span></div><div class='line' id='LC196'><br/></div><div class='line' id='LC197'>			<span class="nx">safe_update</span><span class="p">(</span></div><div class='line' id='LC198'>				<span class="s1">&#39;txp_users&#39;</span><span class="p">,</span></div><div class='line' id='LC199'>				<span class="s2">&quot;nonce=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nb">md5</span><span class="p">(</span><span class="nb">uniqid</span><span class="p">(</span><span class="nx">mt_rand</span><span class="p">(),</span> <span class="k">TRUE</span><span class="p">)))</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span><span class="p">,</span></div><div class='line' id='LC200'>				<span class="s2">&quot;name=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$is_logged_in</span><span class="p">[</span><span class="s1">&#39;name&#39;</span><span class="p">])</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span></div><div class='line' id='LC201'>			<span class="p">);</span></div><div class='line' id='LC202'><br/></div><div class='line' id='LC203'>			<span class="nx">setcookie</span><span class="p">(</span><span class="s1">&#39;txp_login_public&#39;</span><span class="p">,</span> <span class="s1">&#39;&#39;</span><span class="p">,</span> <span class="nb">time</span><span class="p">()</span><span class="o">-</span><span class="mi">3600</span><span class="p">,</span> <span class="nx">mck_login_pub_path</span><span class="p">);</span></div><div class='line' id='LC204'>			<span class="nx">setcookie</span><span class="p">(</span><span class="s1">&#39;txp_login&#39;</span><span class="p">,</span> <span class="s1">&#39;&#39;</span><span class="p">,</span> <span class="nb">time</span><span class="p">()</span><span class="o">-</span><span class="mi">3600</span><span class="p">,</span> <span class="nx">mck_login_admin_path</span><span class="p">,</span> <span class="nx">mck_login_admin_domain</span><span class="p">);</span></div><div class='line' id='LC205'><br/></div><div class='line' id='LC206'>			<span class="nv">$_COOKIE</span><span class="p">[</span><span class="s1">&#39;txp_login_public&#39;</span><span class="p">]</span> <span class="o">=</span> <span class="s1">&#39;&#39;</span><span class="p">;</span></div><div class='line' id='LC207'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC208'>		<span class="p">}</span></div><div class='line' id='LC209'><br/></div><div class='line' id='LC210'>		<span class="cm">/*</span></div><div class='line' id='LC211'><span class="cm">			Log in</span></div><div class='line' id='LC212'><span class="cm">		*/</span></div><div class='line' id='LC213'><br/></div><div class='line' id='LC214'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$form</span> <span class="o">||</span> <span class="nv">$is_logged_in</span> <span class="o">||</span> <span class="o">!</span><span class="nb">strpos</span><span class="p">(</span><span class="nv">$form</span><span class="p">,</span> <span class="s1">&#39;;&#39;</span><span class="p">))</span></div><div class='line' id='LC215'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC216'><br/></div><div class='line' id='LC217'>		<span class="nx">self</span><span class="o">::</span><span class="nv">$action</span> <span class="o">=</span> <span class="s1">&#39;login&#39;</span><span class="p">;</span></div><div class='line' id='LC218'><br/></div><div class='line' id='LC219'>		<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.login&#39;</span><span class="p">);</span></div><div class='line' id='LC220'><br/></div><div class='line' id='LC221'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$pass</span> <span class="o">||</span> <span class="o">!</span><span class="nv">$name</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC222'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;name_and_pass_required&#39;</span><span class="p">);</span></div><div class='line' id='LC223'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC224'>		<span class="p">}</span></div><div class='line' id='LC225'><br/></div><div class='line' id='LC226'>		<span class="nv">$form</span> <span class="o">=</span> <span class="nb">explode</span><span class="p">(</span><span class="s1">&#39;;&#39;</span><span class="p">,</span> <span class="p">(</span><span class="nx">string</span><span class="p">)</span> <span class="nv">$form</span><span class="p">);</span></div><div class='line' id='LC227'><br/></div><div class='line' id='LC228'>		<span class="k">if</span><span class="p">(</span><span class="nv">$form</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">!=</span> <span class="nb">md5</span><span class="p">(</span><span class="nv">$form</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">.</span> <span class="nx">get_pref</span><span class="p">(</span><span class="s1">&#39;blog_uid&#39;</span><span class="p">)))</span> <span class="p">{</span></div><div class='line' id='LC229'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_token&#39;</span><span class="p">);</span></div><div class='line' id='LC230'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC231'>		<span class="p">}</span></div><div class='line' id='LC232'><br/></div><div class='line' id='LC233'>		<span class="k">if</span><span class="p">((</span><span class="nx">int</span><span class="p">)</span> <span class="nv">$form</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">&lt;</span> <span class="o">@</span><span class="nb">strtotime</span><span class="p">(</span><span class="s1">&#39;-30 minutes&#39;</span><span class="p">))</span> <span class="p">{</span></div><div class='line' id='LC234'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;form_expired&#39;</span><span class="p">);</span></div><div class='line' id='LC235'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC236'>		<span class="p">}</span></div><div class='line' id='LC237'><br/></div><div class='line' id='LC238'>		<span class="k">include_once</span> <span class="nx">txpath</span> <span class="o">.</span> <span class="s1">&#39;/include/txp_auth.php&#39;</span><span class="p">;</span></div><div class='line' id='LC239'><br/></div><div class='line' id='LC240'>		<span class="k">if</span><span class="p">(</span><span class="nx">txp_validate</span><span class="p">(</span><span class="nv">$name</span><span class="p">,</span> <span class="nv">$pass</span><span class="p">,</span> <span class="k">false</span><span class="p">)</span> <span class="o">===</span> <span class="k">false</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC241'>			<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.invalid_login&#39;</span><span class="p">);</span></div><div class='line' id='LC242'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_login&#39;</span><span class="p">);</span></div><div class='line' id='LC243'>			<span class="nb">sleep</span><span class="p">(</span><span class="mi">3</span><span class="p">);</span></div><div class='line' id='LC244'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC245'>		<span class="p">}</span></div><div class='line' id='LC246'><br/></div><div class='line' id='LC247'>		<span class="nv">$c_hash</span> <span class="o">=</span> <span class="nb">md5</span><span class="p">(</span><span class="nb">uniqid</span><span class="p">(</span><span class="nx">mt_rand</span><span class="p">(),</span> <span class="k">true</span><span class="p">));</span></div><div class='line' id='LC248'>		<span class="nv">$nonce</span> <span class="o">=</span> <span class="nb">md5</span><span class="p">(</span><span class="nv">$name</span><span class="o">.</span><span class="nb">pack</span><span class="p">(</span><span class="s1">&#39;H*&#39;</span><span class="p">,</span> <span class="nv">$c_hash</span><span class="p">));</span></div><div class='line' id='LC249'>		<span class="nv">$value</span> <span class="o">=</span> <span class="nx">substr</span><span class="p">(</span><span class="nb">md5</span><span class="p">(</span><span class="nv">$nonce</span><span class="p">),</span> <span class="o">-</span><span class="mi">10</span><span class="p">)</span><span class="o">.</span><span class="nv">$name</span><span class="p">;</span></div><div class='line' id='LC250'>		<span class="nv">$privs</span> <span class="o">=</span> <span class="nx">fetch</span><span class="p">(</span><span class="s1">&#39;privs&#39;</span><span class="p">,</span> <span class="s1">&#39;txp_users&#39;</span><span class="p">,</span> <span class="s1">&#39;name&#39;</span><span class="p">,</span> <span class="nv">$name</span><span class="p">);</span></div><div class='line' id='LC251'><br/></div><div class='line' id='LC252'>		<span class="nx">safe_update</span><span class="p">(</span></div><div class='line' id='LC253'>			<span class="s1">&#39;txp_users&#39;</span><span class="p">,</span></div><div class='line' id='LC254'>			<span class="s2">&quot;nonce=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$nonce</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;,</span></div><div class='line' id='LC255'><span class="s2">			last_access=now()&quot;</span><span class="p">,</span></div><div class='line' id='LC256'>			<span class="s2">&quot;name=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span></div><div class='line' id='LC257'>		<span class="p">);</span></div><div class='line' id='LC258'><br/></div><div class='line' id='LC259'>		<span class="nx">setcookie</span><span class="p">(</span></div><div class='line' id='LC260'>			<span class="s1">&#39;txp_login_public&#39;</span><span class="p">,</span></div><div class='line' id='LC261'>			<span class="nv">$value</span><span class="p">,</span></div><div class='line' id='LC262'>			<span class="nv">$stay</span> <span class="o">?</span> <span class="nb">time</span><span class="p">()</span><span class="o">+</span><span class="mi">3600</span><span class="o">*</span><span class="mi">24</span><span class="o">*</span><span class="mi">30</span> <span class="o">:</span> <span class="mi">0</span><span class="p">,</span></div><div class='line' id='LC263'>			<span class="nx">mck_login_pub_path</span></div><div class='line' id='LC264'>		<span class="p">);</span></div><div class='line' id='LC265'><br/></div><div class='line' id='LC266'>		<span class="k">if</span><span class="p">(</span><span class="nv">$privs</span> <span class="o">&gt;</span> <span class="mi">0</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC267'>			<span class="nx">setcookie</span><span class="p">(</span></div><div class='line' id='LC268'>				<span class="s1">&#39;txp_login&#39;</span><span class="p">,</span></div><div class='line' id='LC269'>				<span class="nv">$name</span><span class="o">.</span><span class="s1">&#39;,&#39;</span><span class="o">.</span><span class="nv">$c_hash</span><span class="p">,</span></div><div class='line' id='LC270'>				<span class="nv">$stay</span> <span class="o">?</span> <span class="nb">time</span><span class="p">()</span><span class="o">+</span><span class="mi">3600</span><span class="o">*</span><span class="mi">24</span><span class="o">*</span><span class="mi">365</span> <span class="o">:</span> <span class="mi">0</span><span class="p">,</span></div><div class='line' id='LC271'>				<span class="nx">mck_login_admin_path</span><span class="p">,</span></div><div class='line' id='LC272'>				<span class="nx">mck_login_admin_domain</span></div><div class='line' id='LC273'>			<span class="p">);</span></div><div class='line' id='LC274'>		<span class="p">}</span></div><div class='line' id='LC275'><br/></div><div class='line' id='LC276'>		<span class="nv">$_COOKIE</span><span class="p">[</span><span class="s1">&#39;txp_login_public&#39;</span><span class="p">]</span> <span class="o">=</span> <span class="nv">$value</span><span class="p">;</span></div><div class='line' id='LC277'><br/></div><div class='line' id='LC278'>		<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.logged_in&#39;</span><span class="p">);</span></div><div class='line' id='LC279'>	<span class="p">}</span></div><div class='line' id='LC280'><br/></div><div class='line' id='LC281'>	<span class="sd">/**</span></div><div class='line' id='LC282'><span class="sd">	 * Send password reset confirmation message</span></div><div class='line' id='LC283'><span class="sd">	 * @param array $atts</span></div><div class='line' id='LC284'><span class="sd">	 * @return bool</span></div><div class='line' id='LC285'><span class="sd">	 * @access private</span></div><div class='line' id='LC286'><span class="sd">	 */</span></div><div class='line' id='LC287'><br/></div><div class='line' id='LC288'>	<span class="k">static</span> <span class="k">public</span> <span class="k">function</span> <span class="nf">send_reset</span><span class="p">(</span><span class="nv">$atts</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC289'><br/></div><div class='line' id='LC290'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">doArray</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC291'>			<span class="s1">&#39;name&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_reset_name&#39;</span><span class="p">),</span></div><div class='line' id='LC292'>			<span class="s1">&#39;form&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_reset_form&#39;</span><span class="p">),</span></div><div class='line' id='LC293'>		<span class="p">),</span> <span class="s1">&#39;trim&#39;</span><span class="p">));</span></div><div class='line' id='LC294'><br/></div><div class='line' id='LC295'>		<span class="nv">$is_logged_in</span> <span class="o">=</span> <span class="nx">mck_login</span><span class="p">(</span><span class="k">true</span><span class="p">)</span> <span class="o">!==</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC296'><br/></div><div class='line' id='LC297'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$form</span> <span class="o">||</span> <span class="o">!</span><span class="nb">strpos</span><span class="p">(</span><span class="nv">$form</span><span class="p">,</span> <span class="s1">&#39;;&#39;</span><span class="p">)</span> <span class="o">||</span> <span class="nv">$is_logged_in</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC298'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC299'>		<span class="p">}</span></div><div class='line' id='LC300'><br/></div><div class='line' id='LC301'>		<span class="nx">self</span><span class="o">::</span><span class="nv">$action</span> <span class="o">=</span> <span class="s1">&#39;reset&#39;</span><span class="p">;</span></div><div class='line' id='LC302'><br/></div><div class='line' id='LC303'>		<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.reset&#39;</span><span class="p">);</span></div><div class='line' id='LC304'><br/></div><div class='line' id='LC305'>		<span class="nv">$form</span> <span class="o">=</span> <span class="nb">explode</span><span class="p">(</span><span class="s1">&#39;;&#39;</span><span class="p">,</span> <span class="p">(</span><span class="nx">string</span><span class="p">)</span> <span class="nv">$form</span><span class="p">);</span></div><div class='line' id='LC306'><br/></div><div class='line' id='LC307'>		<span class="k">if</span><span class="p">(</span><span class="nv">$form</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">!=</span> <span class="nb">md5</span><span class="p">(</span><span class="nv">$form</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">.</span> <span class="nx">get_pref</span><span class="p">(</span><span class="s1">&#39;blog_uid&#39;</span><span class="p">)))</span> <span class="p">{</span></div><div class='line' id='LC308'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_token&#39;</span><span class="p">);</span></div><div class='line' id='LC309'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC310'>		<span class="p">}</span></div><div class='line' id='LC311'><br/></div><div class='line' id='LC312'>		<span class="k">if</span><span class="p">((</span><span class="nx">int</span><span class="p">)</span> <span class="nv">$form</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">&lt;</span> <span class="o">@</span><span class="nb">strtotime</span><span class="p">(</span><span class="s1">&#39;-30 minutes&#39;</span><span class="p">))</span> <span class="p">{</span></div><div class='line' id='LC313'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;form_expired&#39;</span><span class="p">);</span></div><div class='line' id='LC314'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC315'>		<span class="p">}</span></div><div class='line' id='LC316'><br/></div><div class='line' id='LC317'>		<span class="nv">$r</span> <span class="o">=</span> </div><div class='line' id='LC318'>			<span class="nx">safe_row</span><span class="p">(</span></div><div class='line' id='LC319'>				<span class="s1">&#39;email, nonce&#39;</span><span class="p">,</span></div><div class='line' id='LC320'>				<span class="s1">&#39;txp_users&#39;</span><span class="p">,</span></div><div class='line' id='LC321'>				<span class="s2">&quot;name=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span></div><div class='line' id='LC322'>			<span class="p">);</span></div><div class='line' id='LC323'><br/></div><div class='line' id='LC324'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$r</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC325'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_username&#39;</span><span class="p">);</span></div><div class='line' id='LC326'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC327'>		<span class="p">}</span></div><div class='line' id='LC328'><br/></div><div class='line' id='LC329'>		<span class="nv">$confirm</span> <span class="o">=</span> </div><div class='line' id='LC330'>			<span class="nb">bin2hex</span><span class="p">(</span></div><div class='line' id='LC331'>				<span class="nb">pack</span><span class="p">(</span><span class="s1">&#39;H*&#39;</span><span class="p">,</span> <span class="nx">substr</span><span class="p">(</span><span class="nb">md5</span><span class="p">(</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;nonce&#39;</span><span class="p">]</span> <span class="o">.</span> <span class="nv">$atts</span><span class="p">[</span><span class="s1">&#39;go_to_after&#39;</span><span class="p">]),</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">10</span><span class="p">))</span><span class="o">.</span> </div><div class='line' id='LC332'>				<span class="nv">$name</span> <span class="o">.</span> <span class="s1">&#39;;&#39;</span> <span class="o">.</span> <span class="nv">$atts</span><span class="p">[</span><span class="s1">&#39;go_to_after&#39;</span><span class="p">]</span></div><div class='line' id='LC333'>			<span class="p">);</span></div><div class='line' id='LC334'><br/></div><div class='line' id='LC335'>		<span class="nv">$message</span> <span class="o">=</span> </div><div class='line' id='LC336'>			<span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;greeting&#39;</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39; &#39;</span><span class="o">.</span><span class="nv">$name</span><span class="o">.</span><span class="s1">&#39;,&#39;</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC337'>			<span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;password_reset_confirmation&#39;</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;: &#39;</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC338'>			<span class="nx">hu</span><span class="o">.</span><span class="s1">&#39;?mck_reset=&#39;</span><span class="o">.</span><span class="nv">$confirm</span><span class="p">;</span></div><div class='line' id='LC339'><br/></div><div class='line' id='LC340'>		<span class="k">if</span><span class="p">(</span><span class="nx">txpMail</span><span class="p">(</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;email&#39;</span><span class="p">],</span> <span class="nv">$atts</span><span class="p">[</span><span class="s1">&#39;subject&#39;</span><span class="p">],</span> <span class="nv">$message</span><span class="p">)</span> <span class="o">===</span> <span class="k">false</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC341'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;could_not_mail&#39;</span><span class="p">);</span></div><div class='line' id='LC342'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC343'>		<span class="p">}</span></div><div class='line' id='LC344'><br/></div><div class='line' id='LC345'>		<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.reset_sent&#39;</span><span class="p">);</span></div><div class='line' id='LC346'>		<span class="k">return</span> <span class="k">true</span><span class="p">;</span></div><div class='line' id='LC347'>	<span class="p">}</span></div><div class='line' id='LC348'><br/></div><div class='line' id='LC349'>	<span class="sd">/**</span></div><div class='line' id='LC350'><span class="sd">	 * Save a new user</span></div><div class='line' id='LC351'><span class="sd">	 * @param array $atts</span></div><div class='line' id='LC352'><span class="sd">	 * @return bool</span></div><div class='line' id='LC353'><span class="sd">	 * @see generate_password(), txp_hash_password()</span></div><div class='line' id='LC354'><span class="sd">	 * @access private</span></div><div class='line' id='LC355'><span class="sd">	 */</span></div><div class='line' id='LC356'><br/></div><div class='line' id='LC357'>	<span class="k">static</span> <span class="k">public</span> <span class="k">function</span> <span class="nf">add_user</span><span class="p">(</span><span class="nv">$atts</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC358'><br/></div><div class='line' id='LC359'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">doArray</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC360'>			<span class="s1">&#39;email&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_register_email&#39;</span><span class="p">),</span></div><div class='line' id='LC361'>			<span class="s1">&#39;name&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_register_name&#39;</span><span class="p">),</span></div><div class='line' id='LC362'>			<span class="s1">&#39;RealName&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_register_realname&#39;</span><span class="p">),</span></div><div class='line' id='LC363'>			<span class="s1">&#39;form&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_register_form&#39;</span><span class="p">),</span></div><div class='line' id='LC364'>		<span class="p">),</span> <span class="s1">&#39;trim&#39;</span><span class="p">));</span></div><div class='line' id='LC365'><br/></div><div class='line' id='LC366'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$form</span> <span class="o">||</span> <span class="o">!</span><span class="nb">strpos</span><span class="p">(</span><span class="nv">$form</span><span class="p">,</span> <span class="s1">&#39;;&#39;</span><span class="p">))</span></div><div class='line' id='LC367'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC368'><br/></div><div class='line' id='LC369'>		<span class="nx">self</span><span class="o">::</span><span class="nv">$action</span> <span class="o">=</span> <span class="s1">&#39;register&#39;</span><span class="p">;</span></div><div class='line' id='LC370'><br/></div><div class='line' id='LC371'>		<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.register&#39;</span><span class="p">);</span></div><div class='line' id='LC372'><br/></div><div class='line' id='LC373'>		<span class="k">if</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="nv">$form_errors</span><span class="p">)</span></div><div class='line' id='LC374'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC375'><br/></div><div class='line' id='LC376'>		<span class="nv">$ip</span> <span class="o">=</span> <span class="nx">remote_addr</span><span class="p">();</span></div><div class='line' id='LC377'><br/></div><div class='line' id='LC378'>		<span class="k">if</span><span class="p">(</span><span class="nx">is_blacklisted</span><span class="p">(</span><span class="nv">$ip</span><span class="p">))</span> <span class="p">{</span></div><div class='line' id='LC379'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;ip_blacklisted&#39;</span><span class="p">);</span></div><div class='line' id='LC380'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC381'>		<span class="p">}</span></div><div class='line' id='LC382'><br/></div><div class='line' id='LC383'>		<span class="k">if</span><span class="p">(</span><span class="nx">fetch</span><span class="p">(</span><span class="s1">&#39;ip&#39;</span><span class="p">,</span> <span class="s1">&#39;txp_discuss_ipban&#39;</span><span class="p">,</span> <span class="s1">&#39;ip&#39;</span><span class="p">,</span> <span class="nv">$ip</span><span class="p">))</span> <span class="p">{</span></div><div class='line' id='LC384'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;you_have_been_banned&#39;</span><span class="p">);</span></div><div class='line' id='LC385'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC386'>		<span class="p">}</span></div><div class='line' id='LC387'><br/></div><div class='line' id='LC388'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$email</span> <span class="o">||</span> <span class="o">!</span><span class="nv">$name</span> <span class="o">||</span> <span class="o">!</span><span class="nv">$RealName</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC389'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;all_fields_required&#39;</span><span class="p">);</span></div><div class='line' id='LC390'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC391'>		<span class="p">}</span></div><div class='line' id='LC392'><br/></div><div class='line' id='LC393'>		<span class="nv">$form</span> <span class="o">=</span> <span class="nb">explode</span><span class="p">(</span><span class="s1">&#39;;&#39;</span><span class="p">,</span> <span class="p">(</span><span class="nx">string</span><span class="p">)</span> <span class="nv">$form</span><span class="p">);</span></div><div class='line' id='LC394'><br/></div><div class='line' id='LC395'>		<span class="k">if</span><span class="p">(</span><span class="nv">$form</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">!=</span> <span class="nb">md5</span><span class="p">(</span><span class="nv">$form</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">.</span> <span class="nx">get_pref</span><span class="p">(</span><span class="s1">&#39;blog_uid&#39;</span><span class="p">)))</span> <span class="p">{</span></div><div class='line' id='LC396'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_token&#39;</span><span class="p">);</span></div><div class='line' id='LC397'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC398'>		<span class="p">}</span></div><div class='line' id='LC399'><br/></div><div class='line' id='LC400'>		<span class="k">if</span><span class="p">((</span><span class="nx">int</span><span class="p">)</span> <span class="nv">$form</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">&lt;</span> <span class="o">@</span><span class="nb">strtotime</span><span class="p">(</span><span class="s1">&#39;-30 minutes&#39;</span><span class="p">))</span> <span class="p">{</span></div><div class='line' id='LC401'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;form_expired&#39;</span><span class="p">);</span></div><div class='line' id='LC402'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC403'>		<span class="p">}</span></div><div class='line' id='LC404'><br/></div><div class='line' id='LC405'>		<span class="k">if</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="na">field_strlen</span><span class="p">(</span><span class="nv">$email</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">100</span><span class="p">)</span></div><div class='line' id='LC406'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;email_too_long&#39;</span><span class="p">);</span></div><div class='line' id='LC407'><br/></div><div class='line' id='LC408'>		<span class="k">elseif</span><span class="p">(</span><span class="o">!</span><span class="nx">is_valid_email</span><span class="p">(</span><span class="nv">$email</span><span class="p">))</span></div><div class='line' id='LC409'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_email&#39;</span><span class="p">);</span></div><div class='line' id='LC410'><br/></div><div class='line' id='LC411'>		<span class="k">if</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="na">field_strlen</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">3</span><span class="p">)</span></div><div class='line' id='LC412'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;username_too_short&#39;</span><span class="p">);</span></div><div class='line' id='LC413'><br/></div><div class='line' id='LC414'>		<span class="k">elseif</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="na">field_strlen</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">64</span><span class="p">)</span></div><div class='line' id='LC415'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;username_too_long&#39;</span><span class="p">);</span></div><div class='line' id='LC416'><br/></div><div class='line' id='LC417'>		<span class="k">if</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="na">field_strlen</span><span class="p">(</span><span class="nv">$RealName</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">64</span><span class="p">)</span></div><div class='line' id='LC418'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;realname_too_long&#39;</span><span class="p">);</span></div><div class='line' id='LC419'><br/></div><div class='line' id='LC420'>		<span class="k">if</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC421'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC422'><br/></div><div class='line' id='LC423'>		<span class="k">if</span><span class="p">(</span></div><div class='line' id='LC424'>			<span class="nx">safe_row</span><span class="p">(</span></div><div class='line' id='LC425'>				<span class="s1">&#39;name&#39;</span><span class="p">,</span> </div><div class='line' id='LC426'>				<span class="s1">&#39;txp_users&#39;</span><span class="p">,</span></div><div class='line' id='LC427'>				<span class="s2">&quot;name=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39; OR email=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$email</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39; LIMIT 0, 1&quot;</span></div><div class='line' id='LC428'>			<span class="p">)</span></div><div class='line' id='LC429'>		<span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC430'><br/></div><div class='line' id='LC431'>			<span class="k">if</span><span class="p">(</span><span class="nx">fetch</span><span class="p">(</span><span class="s1">&#39;email&#39;</span><span class="p">,</span> <span class="s1">&#39;txp_users&#39;</span><span class="p">,</span> <span class="s1">&#39;email&#39;</span><span class="p">,</span> <span class="nv">$email</span><span class="p">))</span> <span class="p">{</span></div><div class='line' id='LC432'>				<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;email_in_use&#39;</span><span class="p">);</span></div><div class='line' id='LC433'>			<span class="p">}</span></div><div class='line' id='LC434'><br/></div><div class='line' id='LC435'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;username_taken&#39;</span><span class="p">);</span></div><div class='line' id='LC436'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC437'>		<span class="p">}</span></div><div class='line' id='LC438'><br/></div><div class='line' id='LC439'>		<span class="nb">sleep</span><span class="p">(</span><span class="mi">3</span><span class="p">);</span></div><div class='line' id='LC440'><br/></div><div class='line' id='LC441'>		<span class="k">include_once</span> <span class="nx">txpath</span> <span class="o">.</span> <span class="s1">&#39;/lib/txplib_admin.php&#39;</span><span class="p">;</span></div><div class='line' id='LC442'>		<span class="k">include_once</span> <span class="nx">txpath</span> <span class="o">.</span> <span class="s1">&#39;/include/txp_auth.php&#39;</span><span class="p">;</span></div><div class='line' id='LC443'><br/></div><div class='line' id='LC444'>		<span class="nv">$password</span> <span class="o">=</span> <span class="nx">generate_password</span><span class="p">(</span><span class="mi">12</span><span class="p">);</span></div><div class='line' id='LC445'>		<span class="nv">$hash</span> <span class="o">=</span> <span class="nx">txp_hash_password</span><span class="p">(</span><span class="nv">$password</span><span class="p">);</span></div><div class='line' id='LC446'>		<span class="nv">$privs</span> <span class="o">=</span> <span class="p">(</span><span class="nx">int</span><span class="p">)</span> <span class="nv">$atts</span><span class="p">[</span><span class="s1">&#39;privs&#39;</span><span class="p">];</span></div><div class='line' id='LC447'><br/></div><div class='line' id='LC448'>		<span class="k">if</span><span class="p">(</span></div><div class='line' id='LC449'>			<span class="nx">safe_insert</span><span class="p">(</span></div><div class='line' id='LC450'>				<span class="s1">&#39;txp_users&#39;</span><span class="p">,</span></div><div class='line' id='LC451'>				<span class="s2">&quot;privs=&#39;</span><span class="si">{</span><span class="nv">$privs</span><span class="si">}</span><span class="s2">&#39;, </span></div><div class='line' id='LC452'><span class="s2">				name=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;,</span></div><div class='line' id='LC453'><span class="s2">				email=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$email</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;,</span></div><div class='line' id='LC454'><span class="s2">				RealName=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$RealName</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;,</span></div><div class='line' id='LC455'><span class="s2">				nonce=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nb">md5</span><span class="p">(</span><span class="nb">uniqid</span><span class="p">(</span><span class="nx">mt_rand</span><span class="p">(),</span> <span class="k">true</span><span class="p">)))</span><span class="o">.</span><span class="s2">&quot;&#39;,</span></div><div class='line' id='LC456'><span class="s2">				pass=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$hash</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span></div><div class='line' id='LC457'>			<span class="p">)</span> <span class="o">===</span> <span class="k">false</span></div><div class='line' id='LC458'>		<span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC459'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;saving_failed&#39;</span><span class="p">);</span></div><div class='line' id='LC460'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC461'>		<span class="p">}</span></div><div class='line' id='LC462'><br/></div><div class='line' id='LC463'>		<span class="nv">$message</span> <span class="o">=</span> </div><div class='line' id='LC464'>			<span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;greeting&#39;</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39; &#39;</span><span class="o">.</span><span class="nv">$name</span><span class="o">.</span><span class="s1">&#39;,&#39;</span><span class="o">.</span></div><div class='line' id='LC465'>			<span class="nx">n</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span><span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;your_password_is&#39;</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;: &#39;</span><span class="o">.</span><span class="nv">$password</span><span class="o">.</span></div><div class='line' id='LC466'>			<span class="nx">n</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span><span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;log_in_at&#39;</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;: &#39;</span><span class="o">.</span><span class="nv">$atts</span><span class="p">[</span><span class="s1">&#39;log_in_url&#39;</span><span class="p">];</span></div><div class='line' id='LC467'><br/></div><div class='line' id='LC468'>		<span class="k">if</span><span class="p">(</span><span class="nx">txpMail</span><span class="p">(</span><span class="nv">$email</span><span class="p">,</span> <span class="nv">$atts</span><span class="p">[</span><span class="s1">&#39;subject&#39;</span><span class="p">],</span> <span class="nv">$message</span><span class="p">)</span> <span class="o">===</span> <span class="k">false</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC469'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;could_not_mail&#39;</span><span class="p">);</span></div><div class='line' id='LC470'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC471'>		<span class="p">}</span></div><div class='line' id='LC472'><br/></div><div class='line' id='LC473'>		<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.registered&#39;</span><span class="p">);</span></div><div class='line' id='LC474'>		<span class="k">return</span> <span class="k">true</span><span class="p">;</span></div><div class='line' id='LC475'>	<span class="p">}</span></div><div class='line' id='LC476'><br/></div><div class='line' id='LC477'>	<span class="sd">/**</span></div><div class='line' id='LC478'><span class="sd">	 * Save a new password</span></div><div class='line' id='LC479'><span class="sd">	 * @return bool</span></div><div class='line' id='LC480'><span class="sd">	 * @see txp_validate(), txp_hash_password()</span></div><div class='line' id='LC481'><span class="sd">	 * @access private</span></div><div class='line' id='LC482'><span class="sd">	 */</span></div><div class='line' id='LC483'><br/></div><div class='line' id='LC484'>	<span class="k">static</span> <span class="k">public</span> <span class="k">function</span> <span class="nf">save_password</span><span class="p">()</span> <span class="p">{</span></div><div class='line' id='LC485'><br/></div><div class='line' id='LC486'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">doArray</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC487'>			<span class="s1">&#39;old_pass&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_password_old&#39;</span><span class="p">),</span></div><div class='line' id='LC488'>			<span class="s1">&#39;new_pass&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_password_new&#39;</span><span class="p">),</span></div><div class='line' id='LC489'>			<span class="s1">&#39;confirm_pass&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_password_confirm&#39;</span><span class="p">),</span></div><div class='line' id='LC490'>			<span class="s1">&#39;token&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_login_token&#39;</span><span class="p">),</span></div><div class='line' id='LC491'>			<span class="s1">&#39;form&#39;</span> <span class="o">=&gt;</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_password_form&#39;</span><span class="p">),</span></div><div class='line' id='LC492'>		<span class="p">),</span> <span class="s1">&#39;trim&#39;</span><span class="p">));</span></div><div class='line' id='LC493'><br/></div><div class='line' id='LC494'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$form</span> <span class="o">||</span> <span class="nx">mck_login</span><span class="p">(</span><span class="k">true</span><span class="p">)</span> <span class="o">===</span> <span class="k">false</span><span class="p">)</span></div><div class='line' id='LC495'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC496'><br/></div><div class='line' id='LC497'>		<span class="nx">self</span><span class="o">::</span><span class="nv">$action</span> <span class="o">=</span> <span class="s1">&#39;password&#39;</span><span class="p">;</span></div><div class='line' id='LC498'><br/></div><div class='line' id='LC499'>		<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.save_password&#39;</span><span class="p">);</span></div><div class='line' id='LC500'><br/></div><div class='line' id='LC501'>		<span class="k">if</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC502'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC503'><br/></div><div class='line' id='LC504'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$old_pass</span> <span class="o">||</span> <span class="o">!</span><span class="nv">$new_pass</span> <span class="o">||</span> <span class="o">!</span><span class="nv">$confirm_pass</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC505'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;all_fields_required&#39;</span><span class="p">);</span></div><div class='line' id='LC506'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC507'>		<span class="p">}</span></div><div class='line' id='LC508'><br/></div><div class='line' id='LC509'>		<span class="k">if</span><span class="p">(</span><span class="nv">$token</span> <span class="o">!=</span> <span class="nx">mck_login_token</span><span class="p">())</span> <span class="p">{</span></div><div class='line' id='LC510'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;invalid_csrf_token&#39;</span><span class="p">);</span></div><div class='line' id='LC511'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC512'>		<span class="p">}</span></div><div class='line' id='LC513'><br/></div><div class='line' id='LC514'>		<span class="nv">$length</span> <span class="o">=</span> <span class="nb">function_exists</span><span class="p">(</span><span class="s1">&#39;mb_strlen&#39;</span><span class="p">)</span> <span class="o">?</span> </div><div class='line' id='LC515'>			<span class="nb">mb_strlen</span><span class="p">(</span><span class="nv">$new_pass</span><span class="p">,</span> <span class="s1">&#39;UTF-8&#39;</span><span class="p">)</span> <span class="o">:</span> <span class="nb">strlen</span><span class="p">(</span><span class="nv">$new_pass</span><span class="p">);</span></div><div class='line' id='LC516'><br/></div><div class='line' id='LC517'>		<span class="k">if</span><span class="p">(</span><span class="mi">6</span> <span class="o">&gt;</span> <span class="nv">$length</span><span class="p">)</span></div><div class='line' id='LC518'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;password_too_short&#39;</span><span class="p">);</span></div><div class='line' id='LC519'><br/></div><div class='line' id='LC520'>		<span class="k">if</span><span class="p">(</span><span class="nv">$new_pass</span> <span class="o">!==</span> <span class="nv">$old_pass</span><span class="p">)</span></div><div class='line' id='LC521'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;passwords_do_not_match&#39;</span><span class="p">);</span></div><div class='line' id='LC522'><br/></div><div class='line' id='LC523'>		<span class="nv">$name</span> <span class="o">=</span> <span class="nx">mck_login</span><span class="p">(</span><span class="k">array</span><span class="p">(</span><span class="s1">&#39;name&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;name&#39;</span><span class="p">));</span></div><div class='line' id='LC524'><br/></div><div class='line' id='LC525'>		<span class="k">include_once</span> <span class="nx">txpath</span> <span class="o">.</span> <span class="s1">&#39;/include/txp_auth.php&#39;</span><span class="p">;</span></div><div class='line' id='LC526'><br/></div><div class='line' id='LC527'>		<span class="k">if</span><span class="p">(</span><span class="nx">txp_validate</span><span class="p">(</span><span class="nv">$name</span><span class="p">,</span> <span class="nv">$old_pass</span><span class="p">,</span> <span class="k">false</span><span class="p">)</span> <span class="o">===</span> <span class="k">false</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC528'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;old_password_incorrect&#39;</span><span class="p">);</span></div><div class='line' id='LC529'>			<span class="nb">sleep</span><span class="p">(</span><span class="mi">3</span><span class="p">);</span></div><div class='line' id='LC530'>		<span class="p">}</span></div><div class='line' id='LC531'><br/></div><div class='line' id='LC532'>		<span class="k">if</span><span class="p">(</span><span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC533'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC534'><br/></div><div class='line' id='LC535'>		<span class="nv">$hash</span> <span class="o">=</span> <span class="nx">txp_hash_password</span><span class="p">(</span><span class="nv">$new_pass</span><span class="p">);</span></div><div class='line' id='LC536'><br/></div><div class='line' id='LC537'>		<span class="k">if</span><span class="p">(</span></div><div class='line' id='LC538'>			<span class="nx">safe_update</span><span class="p">(</span></div><div class='line' id='LC539'>				<span class="s1">&#39;txp_users&#39;</span><span class="p">,</span></div><div class='line' id='LC540'>				<span class="s2">&quot;pass=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$hash</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span><span class="p">,</span></div><div class='line' id='LC541'>				<span class="s2">&quot;name=&#39;&quot;</span><span class="o">.</span><span class="nx">doSlash</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span><span class="o">.</span><span class="s2">&quot;&#39;&quot;</span></div><div class='line' id='LC542'>			<span class="p">)</span> <span class="o">===</span> <span class="k">false</span></div><div class='line' id='LC543'>		<span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC544'>			<span class="nx">self</span><span class="o">::</span><span class="na">error</span><span class="p">(</span><span class="s1">&#39;saving_failed&#39;</span><span class="p">);</span></div><div class='line' id='LC545'>			<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC546'>		<span class="p">}</span></div><div class='line' id='LC547'><br/></div><div class='line' id='LC548'>		<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.password_saved&#39;</span><span class="p">);</span></div><div class='line' id='LC549'>	<span class="p">}</span></div><div class='line' id='LC550'><br/></div><div class='line' id='LC551'>	<span class="sd">/**</span></div><div class='line' id='LC552'><span class="sd">	 * Get string length for pre-save validation.</span></div><div class='line' id='LC553'><span class="sd">	 * @param string $str</span></div><div class='line' id='LC554'><span class="sd">	 * @return int</span></div><div class='line' id='LC555'><span class="sd">	 * @see DB::DB()</span></div><div class='line' id='LC556'><span class="sd">	 * @access private</span></div><div class='line' id='LC557'><span class="sd">	 */</span></div><div class='line' id='LC558'><br/></div><div class='line' id='LC559'>	<span class="k">static</span> <span class="k">public</span> <span class="k">function</span> <span class="nf">field_strlen</span><span class="p">(</span><span class="nv">$str</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC560'>		<span class="k">global</span> <span class="nv">$DB</span><span class="p">;</span></div><div class='line' id='LC561'><br/></div><div class='line' id='LC562'>		<span class="nv">$version</span> <span class="o">=</span> <span class="p">(</span><span class="nx">int</span><span class="p">)</span> <span class="o">@</span><span class="nv">$DB</span><span class="o">-&gt;</span><span class="na">version</span><span class="p">[</span><span class="mi">0</span><span class="p">];</span></div><div class='line' id='LC563'><br/></div><div class='line' id='LC564'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nb">function_exists</span><span class="p">(</span><span class="s1">&#39;mb_strlen&#39;</span><span class="p">)</span> <span class="o">||</span> <span class="nv">$version</span> <span class="o">&lt;</span> <span class="mi">5</span><span class="p">)</span></div><div class='line' id='LC565'>			<span class="k">return</span> <span class="nb">strlen</span><span class="p">(</span><span class="nv">$str</span><span class="p">);</span></div><div class='line' id='LC566'><br/></div><div class='line' id='LC567'>		<span class="k">return</span> <span class="nb">mb_strlen</span><span class="p">(</span><span class="nv">$str</span><span class="p">,</span> <span class="s1">&#39;UTF-8&#39;</span><span class="p">);</span></div><div class='line' id='LC568'>	<span class="p">}</span></div><div class='line' id='LC569'><span class="p">}</span></div><div class='line' id='LC570'><br/></div><div class='line' id='LC571'><span class="sd">/**</span></div><div class='line' id='LC572'><span class="sd"> * Password reset form</span></div><div class='line' id='LC573'><span class="sd"> * @param array $atts</span></div><div class='line' id='LC574'><span class="sd"> * @param string $atts[action] Form&#39;s action (target location)</span></div><div class='line' id='LC575'><span class="sd"> * @param string $atts[id] Form&#39;s HTML id.</span></div><div class='line' id='LC576'><span class="sd"> * @param string $atts[class] Form&#39;s HTML class.</span></div><div class='line' id='LC577'><span class="sd"> * @param string $atts[go_to_after] The page (page) the confirmation URL directs users. i.e. about/reset-page</span></div><div class='line' id='LC578'><span class="sd"> * @param string $atts[subject] Confirmation email&#39;s subject.</span></div><div class='line' id='LC579'><span class="sd"> * @param string $thing</span></div><div class='line' id='LC580'><span class="sd"> * @return string HTML markup</span></div><div class='line' id='LC581'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC582'><span class="sd"> *		&lt;txp:mck_reset_form&gt;</span></div><div class='line' id='LC583'><span class="sd"> *			&lt;txp:mck_login_errors /&gt;</span></div><div class='line' id='LC584'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;text&quot; name=&quot;mck_reset_name&quot; /&gt;</span></div><div class='line' id='LC585'><span class="sd"> *			&lt;button type=&quot;submit&quot;&gt;Send reset request&lt;/button&gt;</span></div><div class='line' id='LC586'><span class="sd"> *		&lt;txp:else /&gt;</span></div><div class='line' id='LC587'><span class="sd"> *			Confirmation email has been sent with a reset link.</span></div><div class='line' id='LC588'><span class="sd"> *		&lt;/txp:mck_reset_form&gt;</span></div><div class='line' id='LC589'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC590'><span class="sd"> */</span></div><div class='line' id='LC591'><br/></div><div class='line' id='LC592'>	<span class="k">function</span> <span class="nf">mck_reset_form</span><span class="p">(</span><span class="nv">$atts</span><span class="p">,</span> <span class="nv">$thing</span><span class="o">=</span><span class="s1">&#39;&#39;</span><span class="p">){</span></div><div class='line' id='LC593'><br/></div><div class='line' id='LC594'>		<span class="k">global</span> <span class="nv">$pretext</span><span class="p">,</span> <span class="nv">$sitename</span><span class="p">;</span></div><div class='line' id='LC595'><br/></div><div class='line' id='LC596'>		<span class="nv">$opt</span> <span class="o">=</span> <span class="nx">lAtts</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC597'>			<span class="s1">&#39;action&#39;</span> <span class="o">=&gt;</span> <span class="nv">$pretext</span><span class="p">[</span><span class="s1">&#39;request_uri&#39;</span><span class="p">]</span> <span class="o">.</span> <span class="s1">&#39;#mck_reset_form&#39;</span><span class="p">,</span></div><div class='line' id='LC598'>			<span class="s1">&#39;id&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;mck_reset_form&#39;</span><span class="p">,</span></div><div class='line' id='LC599'>			<span class="s1">&#39;class&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;mck_reset_form&#39;</span><span class="p">,</span></div><div class='line' id='LC600'>			<span class="s1">&#39;go_to_after&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;&#39;</span><span class="p">,</span></div><div class='line' id='LC601'>			<span class="s1">&#39;subject&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;[&#39;</span><span class="o">.</span><span class="nv">$sitename</span><span class="o">.</span><span class="s1">&#39;] &#39;</span><span class="o">.</span><span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;password_reset_confirmation_request&#39;</span><span class="p">),</span></div><div class='line' id='LC602'>		<span class="p">),</span> <span class="nv">$atts</span><span class="p">);</span></div><div class='line' id='LC603'><br/></div><div class='line' id='LC604'>		<span class="k">if</span><span class="p">(</span><span class="nx">mck_login</span><span class="p">(</span><span class="k">true</span><span class="p">)</span> <span class="o">!==</span> <span class="k">false</span><span class="p">)</span></div><div class='line' id='LC605'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC606'><br/></div><div class='line' id='LC607'>		<span class="nv">$r</span> <span class="o">=</span> <span class="nx">mck_login</span><span class="o">::</span><span class="na">send_reset</span><span class="p">(</span><span class="nv">$opt</span><span class="p">);</span></div><div class='line' id='LC608'>		<span class="nb">extract</span><span class="p">(</span><span class="nv">$opt</span><span class="p">);</span></div><div class='line' id='LC609'><br/></div><div class='line' id='LC610'>		<span class="k">if</span><span class="p">(</span><span class="nv">$r</span> <span class="o">===</span> <span class="k">true</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC611'>			<span class="k">return</span> <span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="k">false</span><span class="p">));</span></div><div class='line' id='LC612'><br/></div><div class='line' id='LC613'>		<span class="nv">$token</span> <span class="o">=</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_reset_form&#39;</span><span class="p">);</span></div><div class='line' id='LC614'><br/></div><div class='line' id='LC615'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$token</span> <span class="o">||</span> <span class="o">!</span><span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span> <span class="p">{</span></div><div class='line' id='LC616'>			<span class="nv">$timestamp</span> <span class="o">=</span> <span class="nb">strtotime</span><span class="p">(</span><span class="s1">&#39;now&#39;</span><span class="p">);</span></div><div class='line' id='LC617'>			<span class="nv">$token</span> <span class="o">=</span> <span class="nv">$timestamp</span><span class="o">.</span><span class="s1">&#39;;&#39;</span><span class="o">.</span><span class="nb">md5</span><span class="p">(</span><span class="nv">$timestamp</span> <span class="o">.</span> <span class="nx">get_pref</span><span class="p">(</span><span class="s1">&#39;blog_uid&#39;</span><span class="p">));</span></div><div class='line' id='LC618'>		<span class="p">}</span></div><div class='line' id='LC619'><br/></div><div class='line' id='LC620'>		<span class="k">if</span><span class="p">(</span><span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC621'>			<span class="nv">$class</span> <span class="o">.=</span> <span class="s1">&#39; mck_login_error&#39;</span><span class="p">;</span></div><div class='line' id='LC622'><br/></div><div class='line' id='LC623'>		<span class="nx">mck_login_errors</span><span class="p">(</span><span class="s1">&#39;reset&#39;</span><span class="p">);</span></div><div class='line' id='LC624'><br/></div><div class='line' id='LC625'>		<span class="nv">$r</span> <span class="o">=</span></div><div class='line' id='LC626'>			<span class="s1">&#39;&lt;form method=&quot;post&quot; id=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$id</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot; class=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$class</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot; action=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$action</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot;&gt;&#39;</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC627'>				<span class="nx">hInput</span><span class="p">(</span><span class="s1">&#39;mck_reset_form&#39;</span><span class="p">,</span> <span class="nv">$token</span><span class="p">)</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC628'>				<span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="k">true</span><span class="p">))</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC629'>				<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.reset_form&#39;</span><span class="p">)</span><span class="o">.</span></div><div class='line' id='LC630'>			<span class="s1">&#39;&lt;/form&gt;&#39;</span><span class="p">;</span></div><div class='line' id='LC631'><br/></div><div class='line' id='LC632'>		<span class="nx">mck_login_errors</span><span class="p">(</span><span class="k">null</span><span class="p">);</span></div><div class='line' id='LC633'>		<span class="k">return</span> <span class="nv">$r</span><span class="p">;</span></div><div class='line' id='LC634'>	<span class="p">}</span></div><div class='line' id='LC635'><br/></div><div class='line' id='LC636'><span class="sd">/**</span></div><div class='line' id='LC637'><span class="sd"> * Return user data</span></div><div class='line' id='LC638'><span class="sd"> * @param array|bool $atts</span></div><div class='line' id='LC639'><span class="sd"> * @param string $atts[name] Options: name, RealName, email, privs.</span></div><div class='line' id='LC640'><span class="sd"> * @param bool $atts[escape] Convert special characters to HTML entities.</span></div><div class='line' id='LC641'><span class="sd"> * @return mixed</span></div><div class='line' id='LC642'><span class="sd"> * @see is_logged_in()</span></div><div class='line' id='LC643'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC644'><span class="sd"> *		&lt;txp:mck_login name=&quot;email&quot; /&gt;</span></div><div class='line' id='LC645'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC646'><span class="sd"> */</span></div><div class='line' id='LC647'><br/></div><div class='line' id='LC648'>	<span class="k">function</span> <span class="nf">mck_login</span><span class="p">(</span><span class="nv">$atts</span><span class="p">){</span></div><div class='line' id='LC649'>		<span class="k">static</span> <span class="nv">$data</span> <span class="o">=</span> <span class="k">NULL</span><span class="p">;</span></div><div class='line' id='LC650'><br/></div><div class='line' id='LC651'>		<span class="k">if</span><span class="p">(</span><span class="nv">$data</span> <span class="o">===</span> <span class="k">NULL</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC652'>			<span class="nv">$data</span> <span class="o">=</span> <span class="nx">is_logged_in</span><span class="p">();</span></div><div class='line' id='LC653'>		<span class="p">}</span></div><div class='line' id='LC654'><br/></div><div class='line' id='LC655'>		<span class="k">if</span><span class="p">(</span><span class="nv">$atts</span> <span class="o">===</span> <span class="k">true</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC656'>			<span class="k">return</span> <span class="nv">$data</span><span class="p">;</span></div><div class='line' id='LC657'>		<span class="p">}</span></div><div class='line' id='LC658'><br/></div><div class='line' id='LC659'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">lAtts</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC660'>			<span class="s1">&#39;name&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;RealName&#39;</span><span class="p">,</span></div><div class='line' id='LC661'>			<span class="s1">&#39;escape&#39;</span> <span class="o">=&gt;</span> <span class="mi">1</span><span class="p">,</span></div><div class='line' id='LC662'>		<span class="p">),</span><span class="nv">$atts</span><span class="p">));</span></div><div class='line' id='LC663'><br/></div><div class='line' id='LC664'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$data</span> <span class="o">||</span> <span class="o">!</span><span class="nb">isset</span><span class="p">(</span><span class="nv">$data</span><span class="p">[</span><span class="nv">$name</span><span class="p">]))</span></div><div class='line' id='LC665'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC666'><br/></div><div class='line' id='LC667'>		<span class="k">return</span> <span class="nv">$escape</span> <span class="o">?</span> <span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$data</span><span class="p">[</span><span class="nv">$name</span><span class="p">])</span> <span class="o">:</span> <span class="nv">$data</span><span class="p">[</span><span class="nv">$name</span><span class="p">];</span></div><div class='line' id='LC668'>	<span class="p">}</span></div><div class='line' id='LC669'><br/></div><div class='line' id='LC670'><span class="sd">/**</span></div><div class='line' id='LC671'><span class="sd"> * Check if the user is logged in, or that the data matches the value</span></div><div class='line' id='LC672'><span class="sd"> * @param array $atts</span></div><div class='line' id='LC673'><span class="sd"> * @param string $atts[name] If NULL (unset), checks if visitor is logged in.</span></div><div class='line' id='LC674'><span class="sd"> * @param string $atts[value] Match to.</span></div><div class='line' id='LC675'><span class="sd"> * @param string $thing</span></div><div class='line' id='LC676'><span class="sd"> * @return string</span></div><div class='line' id='LC677'><span class="sd"> * @see mck_login()</span></div><div class='line' id='LC678'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC679'><span class="sd"> *		&lt;txp:mck_login_if&gt;</span></div><div class='line' id='LC680'><span class="sd"> *			User is logged in.</span></div><div class='line' id='LC681'><span class="sd"> *		&lt;txp:else /&gt;</span></div><div class='line' id='LC682'><span class="sd"> *			User is not logged in.</span></div><div class='line' id='LC683'><span class="sd"> *		&lt;/txp:mck_login_if&gt;</span></div><div class='line' id='LC684'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC685'><span class="sd"> */</span></div><div class='line' id='LC686'><br/></div><div class='line' id='LC687'>	<span class="k">function</span> <span class="nf">mck_login_if</span><span class="p">(</span><span class="nv">$atts</span><span class="p">,</span> <span class="nv">$thing</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC688'><br/></div><div class='line' id='LC689'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">lAtts</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC690'>			<span class="s1">&#39;name&#39;</span> <span class="o">=&gt;</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC691'>			<span class="s1">&#39;value&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;&#39;</span><span class="p">,</span></div><div class='line' id='LC692'>		<span class="p">),</span><span class="nv">$atts</span><span class="p">));</span></div><div class='line' id='LC693'><br/></div><div class='line' id='LC694'>		<span class="nv">$data</span> <span class="o">=</span> <span class="nx">mck_login</span><span class="p">(</span><span class="k">true</span><span class="p">);</span></div><div class='line' id='LC695'><br/></div><div class='line' id='LC696'>		<span class="k">if</span><span class="p">(</span><span class="nv">$name</span> <span class="o">===</span> <span class="k">NULL</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC697'>			<span class="nv">$r</span> <span class="o">=</span> <span class="nv">$data</span> <span class="o">!==</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC698'>		<span class="p">}</span></div><div class='line' id='LC699'><br/></div><div class='line' id='LC700'>		<span class="k">else</span> <span class="p">{</span></div><div class='line' id='LC701'>			<span class="nv">$r</span> <span class="o">=</span> <span class="nb">isset</span><span class="p">(</span><span class="nv">$data</span><span class="p">[</span><span class="nv">$name</span><span class="p">])</span> <span class="o">&amp;&amp;</span> <span class="nv">$data</span><span class="p">[</span><span class="nv">$name</span><span class="p">]</span> <span class="o">==</span> <span class="nv">$value</span><span class="p">;</span></div><div class='line' id='LC702'>		<span class="p">}</span></div><div class='line' id='LC703'><br/></div><div class='line' id='LC704'>		<span class="k">return</span> <span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="nv">$r</span><span class="p">));</span></div><div class='line' id='LC705'>	<span class="p">}</span></div><div class='line' id='LC706'><br/></div><div class='line' id='LC707'><span class="sd">/**</span></div><div class='line' id='LC708'><span class="sd"> * Register form</span></div><div class='line' id='LC709'><span class="sd"> * @param array $atts</span></div><div class='line' id='LC710'><span class="sd"> * @param int $atts[privs] Privileges the user is created with.</span></div><div class='line' id='LC711'><span class="sd"> * @param string $atts[action] Form&#39;s action (target location).</span></div><div class='line' id='LC712'><span class="sd"> * @param string $atts[id] Form&#39;s HTML id. </span></div><div class='line' id='LC713'><span class="sd"> * @param string $atts[class] Form&#39;s HTML class.</span></div><div class='line' id='LC714'><span class="sd"> * @param string $atts[log_in_url] &quot;Log in at&quot; URL used in the sent email.</span></div><div class='line' id='LC715'><span class="sd"> * @param string $atts[subject] Email message&#39;s subject.</span></div><div class='line' id='LC716'><span class="sd"> * @param string $thing</span></div><div class='line' id='LC717'><span class="sd"> * @return string HTML markup.</span></div><div class='line' id='LC718'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC719'><span class="sd"> *		&lt;txp:mck_register_form&gt;</span></div><div class='line' id='LC720'><span class="sd"> *			&lt;txp:mck_login_errors /&gt;</span></div><div class='line' id='LC721'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;text&quot; name=&quot;mck_register_email&quot; /&gt;</span></div><div class='line' id='LC722'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;text&quot; name=&quot;mck_register_name&quot; /&gt;</span></div><div class='line' id='LC723'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;text&quot; name=&quot;mck_register_realname&quot; /&gt;</span></div><div class='line' id='LC724'><span class="sd"> *			&lt;button type=&quot;submit&quot;&gt;Register&lt;/button&gt;</span></div><div class='line' id='LC725'><span class="sd"> *		&lt;txp:else /&gt;</span></div><div class='line' id='LC726'><span class="sd"> *			Email sent with your login details.</span></div><div class='line' id='LC727'><span class="sd"> *		&lt;/txp:mck_register_form&gt;</span></div><div class='line' id='LC728'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC729'><span class="sd"> */</span></div><div class='line' id='LC730'><br/></div><div class='line' id='LC731'>	<span class="k">function</span> <span class="nf">mck_register_form</span><span class="p">(</span><span class="nv">$atts</span><span class="p">,</span> <span class="nv">$thing</span><span class="o">=</span><span class="s1">&#39;&#39;</span><span class="p">){</span></div><div class='line' id='LC732'><br/></div><div class='line' id='LC733'>		<span class="k">global</span> <span class="nv">$pretext</span><span class="p">,</span> <span class="nv">$sitename</span><span class="p">;</span></div><div class='line' id='LC734'><br/></div><div class='line' id='LC735'>		<span class="nv">$opt</span> <span class="o">=</span> <span class="nx">lAtts</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC736'>			<span class="s1">&#39;privs&#39;</span> <span class="o">=&gt;</span> <span class="mi">0</span><span class="p">,</span></div><div class='line' id='LC737'>			<span class="s1">&#39;action&#39;</span> <span class="o">=&gt;</span> <span class="nv">$pretext</span><span class="p">[</span><span class="s1">&#39;request_uri&#39;</span><span class="p">]</span><span class="o">.</span><span class="s1">&#39;#mck_register_form&#39;</span><span class="p">,</span></div><div class='line' id='LC738'>			<span class="s1">&#39;id&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;mck_register_form&#39;</span><span class="p">,</span></div><div class='line' id='LC739'>			<span class="s1">&#39;class&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;mck_register_form&#39;</span><span class="p">,</span></div><div class='line' id='LC740'>			<span class="s1">&#39;log_in_url&#39;</span> <span class="o">=&gt;</span> <span class="nx">hu</span><span class="p">,</span></div><div class='line' id='LC741'>			<span class="s1">&#39;subject&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;[&#39;</span><span class="o">.</span><span class="nv">$sitename</span><span class="o">.</span><span class="s1">&#39;] &#39;</span><span class="o">.</span><span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;your_new_password&#39;</span><span class="p">),</span></div><div class='line' id='LC742'>		<span class="p">),</span> <span class="nv">$atts</span><span class="p">);</span></div><div class='line' id='LC743'><br/></div><div class='line' id='LC744'>		<span class="nv">$r</span> <span class="o">=</span> <span class="nx">mck_login</span><span class="o">::</span><span class="na">add_user</span><span class="p">(</span><span class="nv">$opt</span><span class="p">);</span></div><div class='line' id='LC745'>		<span class="nb">extract</span><span class="p">(</span><span class="nv">$opt</span><span class="p">);</span></div><div class='line' id='LC746'><br/></div><div class='line' id='LC747'>		<span class="k">if</span><span class="p">(</span><span class="nv">$r</span> <span class="o">===</span> <span class="k">true</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC748'>			<span class="k">return</span> <span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="k">false</span><span class="p">));</span></div><div class='line' id='LC749'><br/></div><div class='line' id='LC750'>		<span class="nv">$token</span> <span class="o">=</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_register_form&#39;</span><span class="p">);</span></div><div class='line' id='LC751'><br/></div><div class='line' id='LC752'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$token</span> <span class="o">||</span> <span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span> <span class="p">{</span></div><div class='line' id='LC753'>			<span class="nv">$timestamp</span> <span class="o">=</span> <span class="nb">strtotime</span><span class="p">(</span><span class="s1">&#39;now&#39;</span><span class="p">);</span></div><div class='line' id='LC754'>			<span class="nv">$token</span> <span class="o">=</span> <span class="nv">$timestamp</span><span class="o">.</span><span class="s1">&#39;;&#39;</span><span class="o">.</span><span class="nb">md5</span><span class="p">(</span><span class="nv">$timestamp</span> <span class="o">.</span> <span class="nx">get_pref</span><span class="p">(</span><span class="s1">&#39;blog_uid&#39;</span><span class="p">));</span></div><div class='line' id='LC755'>		<span class="p">}</span></div><div class='line' id='LC756'><br/></div><div class='line' id='LC757'>		<span class="k">if</span><span class="p">(</span><span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC758'>			<span class="nv">$class</span> <span class="o">.=</span> <span class="s1">&#39; mck_login_error&#39;</span><span class="p">;</span></div><div class='line' id='LC759'><br/></div><div class='line' id='LC760'>		<span class="nx">mck_login_errors</span><span class="p">(</span><span class="s1">&#39;register&#39;</span><span class="p">);</span></div><div class='line' id='LC761'><br/></div><div class='line' id='LC762'>		<span class="nv">$r</span> <span class="o">=</span></div><div class='line' id='LC763'>			<span class="s1">&#39;&lt;form method=&quot;post&quot; id=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$id</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot; class=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$class</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot; action=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$action</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot;&gt;&#39;</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC764'>				<span class="nx">hInput</span><span class="p">(</span><span class="s1">&#39;mck_register_form&#39;</span><span class="p">,</span> <span class="nv">$token</span><span class="p">)</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC765'>				<span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="k">true</span><span class="p">))</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC766'>				<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.register_form&#39;</span><span class="p">)</span><span class="o">.</span></div><div class='line' id='LC767'>			<span class="s1">&#39;&lt;/form&gt;&#39;</span><span class="p">;</span></div><div class='line' id='LC768'><br/></div><div class='line' id='LC769'>		<span class="nx">mck_login_errors</span><span class="p">(</span><span class="k">null</span><span class="p">);</span></div><div class='line' id='LC770'><br/></div><div class='line' id='LC771'>		<span class="k">return</span> <span class="nv">$r</span><span class="p">;</span></div><div class='line' id='LC772'>	<span class="p">}</span></div><div class='line' id='LC773'><br/></div><div class='line' id='LC774'><span class="sd">/**</span></div><div class='line' id='LC775'><span class="sd"> * Displays a login form</span></div><div class='line' id='LC776'><span class="sd"> * @param array $atts</span></div><div class='line' id='LC777'><span class="sd"> * @param string $atts[action] Form&#39;s action (target location).</span></div><div class='line' id='LC778'><span class="sd"> * @param string $atts[id] Form&#39;s HTML id.</span></div><div class='line' id='LC779'><span class="sd"> * @param string $atts[class] Form&#39;s HTML class.</span></div><div class='line' id='LC780'><span class="sd"> * @param string $thing</span></div><div class='line' id='LC781'><span class="sd"> * @return string HTML markup.</span></div><div class='line' id='LC782'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC783'><span class="sd"> *		&lt;txp:mck_login_form&gt;</span></div><div class='line' id='LC784'><span class="sd"> *			&lt;txp:mck_login_errors /&gt;</span></div><div class='line' id='LC785'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;text&quot; name=&quot;mck_login_name&quot; /&gt;</span></div><div class='line' id='LC786'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;password&quot; name=&quot;mck_login_pass&quot; /&gt;</span></div><div class='line' id='LC787'><span class="sd"> *			&lt;button type=&quot;submit&quot;&gt;Log in&lt;/button&gt;</span></div><div class='line' id='LC788'><span class="sd"> *		&lt;txp:else /&gt;</span></div><div class='line' id='LC789'><span class="sd"> *			You are logged in. &lt;a href=&quot;?mck_logout=1&quot;&gt;Log out&lt;/a&gt;.</span></div><div class='line' id='LC790'><span class="sd"> *		&lt;/txp:mck_login_form&gt;</span></div><div class='line' id='LC791'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC792'><span class="sd"> */</span></div><div class='line' id='LC793'><br/></div><div class='line' id='LC794'>	<span class="k">function</span> <span class="nf">mck_login_form</span><span class="p">(</span><span class="nv">$atts</span><span class="p">,</span> <span class="nv">$thing</span><span class="o">=</span><span class="s1">&#39;&#39;</span><span class="p">){</span></div><div class='line' id='LC795'><br/></div><div class='line' id='LC796'>		<span class="k">global</span> <span class="nv">$pretext</span><span class="p">;</span></div><div class='line' id='LC797'><br/></div><div class='line' id='LC798'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">lAtts</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC799'>			<span class="s1">&#39;action&#39;</span> <span class="o">=&gt;</span> <span class="nv">$pretext</span><span class="p">[</span><span class="s1">&#39;request_uri&#39;</span><span class="p">]</span><span class="o">.</span><span class="s1">&#39;#mck_login_form&#39;</span><span class="p">,</span></div><div class='line' id='LC800'>			<span class="s1">&#39;id&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;mck_login_form&#39;</span><span class="p">,</span></div><div class='line' id='LC801'>			<span class="s1">&#39;class&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;mck_login_form&#39;</span><span class="p">,</span></div><div class='line' id='LC802'>		<span class="p">),</span> <span class="nv">$atts</span><span class="p">));</span></div><div class='line' id='LC803'><br/></div><div class='line' id='LC804'>		<span class="k">if</span><span class="p">(</span><span class="nx">mck_login</span><span class="p">(</span><span class="k">true</span><span class="p">)</span> <span class="o">!==</span> <span class="k">false</span><span class="p">)</span></div><div class='line' id='LC805'>			<span class="k">return</span> <span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="k">false</span><span class="p">));</span></div><div class='line' id='LC806'><br/></div><div class='line' id='LC807'>		<span class="nv">$token</span> <span class="o">=</span> <span class="nx">ps</span><span class="p">(</span><span class="s1">&#39;mck_login_form&#39;</span><span class="p">);</span></div><div class='line' id='LC808'><br/></div><div class='line' id='LC809'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$token</span> <span class="o">||</span> <span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span> <span class="p">{</span></div><div class='line' id='LC810'>			<span class="nv">$timestamp</span> <span class="o">=</span> <span class="nb">strtotime</span><span class="p">(</span><span class="s1">&#39;now&#39;</span><span class="p">);</span></div><div class='line' id='LC811'>			<span class="nv">$token</span> <span class="o">=</span> <span class="nv">$timestamp</span><span class="o">.</span><span class="s1">&#39;;&#39;</span><span class="o">.</span><span class="nb">md5</span><span class="p">(</span><span class="nv">$timestamp</span> <span class="o">.</span> <span class="nx">get_pref</span><span class="p">(</span><span class="s1">&#39;blog_uid&#39;</span><span class="p">));</span></div><div class='line' id='LC812'>		<span class="p">}</span></div><div class='line' id='LC813'><br/></div><div class='line' id='LC814'>		<span class="k">if</span><span class="p">(</span><span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC815'>			<span class="nv">$class</span> <span class="o">.=</span> <span class="s1">&#39;mck_login_error&#39;</span><span class="p">;</span></div><div class='line' id='LC816'><br/></div><div class='line' id='LC817'>		<span class="nx">mck_login_errors</span><span class="p">(</span><span class="s1">&#39;login&#39;</span><span class="p">);</span></div><div class='line' id='LC818'><br/></div><div class='line' id='LC819'>		<span class="nv">$thing</span> <span class="o">=</span> </div><div class='line' id='LC820'>			<span class="s1">&#39;&lt;form method=&quot;post&quot; id=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$id</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot; class=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$class</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot; action=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$action</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot;&gt;&#39;</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC821'>				<span class="nx">hInput</span><span class="p">(</span><span class="s1">&#39;mck_login_form&#39;</span><span class="p">,</span> <span class="nv">$token</span><span class="p">)</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC822'>				<span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="k">true</span><span class="p">))</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC823'>				<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.login_form&#39;</span><span class="p">)</span><span class="o">.</span></div><div class='line' id='LC824'>			<span class="s1">&#39;&lt;/form&gt;&#39;</span><span class="p">;</span></div><div class='line' id='LC825'><br/></div><div class='line' id='LC826'>		<span class="nx">mck_login_errors</span><span class="p">(</span><span class="k">null</span><span class="p">);</span></div><div class='line' id='LC827'><br/></div><div class='line' id='LC828'>		<span class="k">return</span> <span class="nv">$thing</span><span class="p">;</span></div><div class='line' id='LC829'>	<span class="p">}</span></div><div class='line' id='LC830'><br/></div><div class='line' id='LC831'><span class="sd">/**</span></div><div class='line' id='LC832'><span class="sd"> * Displays password changing form</span></div><div class='line' id='LC833'><span class="sd"> * @param array $atts</span></div><div class='line' id='LC834'><span class="sd"> * @param string $atts[action] Form&#39;s action (target location).</span></div><div class='line' id='LC835'><span class="sd"> * @param string $atts[id] Form&#39;s HTML id.</span></div><div class='line' id='LC836'><span class="sd"> * @param string $atts[class] Form&#39;s HTML class.</span></div><div class='line' id='LC837'><span class="sd"> * @param string $thing</span></div><div class='line' id='LC838'><span class="sd"> * @return string HTML markup</span></div><div class='line' id='LC839'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC840'><span class="sd"> *		&lt;txp:mck_password_form&gt;</span></div><div class='line' id='LC841'><span class="sd"> *			&lt;txp:mck_login_errors /&gt;</span></div><div class='line' id='LC842'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;password&quot; name=&quot;mck_password_old&quot; /&gt;</span></div><div class='line' id='LC843'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;password&quot; name=&quot;mck_password_new&quot; /&gt;</span></div><div class='line' id='LC844'><span class="sd"> *			&lt;txp:mck_login_input type=&quot;password&quot; name=&quot;mck_password_confirm&quot; /&gt;</span></div><div class='line' id='LC845'><span class="sd"> *			&lt;button type=&quot;submit&quot;&gt;Save new password&lt;/button&gt;</span></div><div class='line' id='LC846'><span class="sd"> *		&lt;txp:else /&gt;</span></div><div class='line' id='LC847'><span class="sd"> *			Password changed.</span></div><div class='line' id='LC848'><span class="sd"> *		&lt;/txp:mck_password_form&gt;</span></div><div class='line' id='LC849'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC850'><span class="sd"> */</span></div><div class='line' id='LC851'><br/></div><div class='line' id='LC852'>	<span class="k">function</span> <span class="nf">mck_password_form</span><span class="p">(</span><span class="nv">$atts</span><span class="p">,</span> <span class="nv">$thing</span><span class="o">=</span><span class="s1">&#39;&#39;</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC853'><br/></div><div class='line' id='LC854'>		<span class="k">global</span> <span class="nv">$pretext</span><span class="p">;</span></div><div class='line' id='LC855'><br/></div><div class='line' id='LC856'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">lAtts</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC857'>			<span class="s1">&#39;action&#39;</span> <span class="o">=&gt;</span> <span class="nv">$pretext</span><span class="p">[</span><span class="s1">&#39;request_uri&#39;</span><span class="p">]</span><span class="o">.</span><span class="s1">&#39;#mck_password_form&#39;</span><span class="p">,</span></div><div class='line' id='LC858'>			<span class="s1">&#39;id&#39;</span><span class="o">=&gt;</span> <span class="s1">&#39;mck_password_form&#39;</span><span class="p">,</span></div><div class='line' id='LC859'>			<span class="s1">&#39;class&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;mck_password_form&#39;</span><span class="p">,</span></div><div class='line' id='LC860'>		<span class="p">),</span> <span class="nv">$atts</span><span class="p">));</span></div><div class='line' id='LC861'><br/></div><div class='line' id='LC862'>		<span class="k">if</span><span class="p">(</span><span class="nx">mck_login</span><span class="p">(</span><span class="k">true</span><span class="p">)</span> <span class="o">===</span> <span class="k">false</span><span class="p">)</span></div><div class='line' id='LC863'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC864'><br/></div><div class='line' id='LC865'>		<span class="nv">$r</span> <span class="o">=</span> <span class="nx">mck_login</span><span class="o">::</span><span class="na">save_password</span><span class="p">();</span></div><div class='line' id='LC866'><br/></div><div class='line' id='LC867'>		<span class="k">if</span><span class="p">(</span><span class="nv">$r</span> <span class="o">===</span> <span class="k">true</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC868'>			<span class="k">return</span> <span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="k">false</span><span class="p">));</span></div><div class='line' id='LC869'><br/></div><div class='line' id='LC870'>		<span class="k">if</span><span class="p">(</span><span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">())</span></div><div class='line' id='LC871'>			<span class="nv">$class</span> <span class="o">.=</span> <span class="s1">&#39;mck_login_error&#39;</span><span class="p">;</span></div><div class='line' id='LC872'><br/></div><div class='line' id='LC873'>		<span class="nx">mck_login_errors</span><span class="p">(</span><span class="s1">&#39;password&#39;</span><span class="p">);</span></div><div class='line' id='LC874'><br/></div><div class='line' id='LC875'>		<span class="nv">$thing</span> <span class="o">=</span> </div><div class='line' id='LC876'>			<span class="s1">&#39;&lt;form method=&quot;post&quot; id=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$id</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot; class=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$class</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot; action=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$action</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot;&gt;&#39;</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC877'>				<span class="nx">hInput</span><span class="p">(</span><span class="s1">&#39;mck_login_token&#39;</span><span class="p">,</span> <span class="nx">mck_login_token</span><span class="p">())</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC878'>				<span class="nx">hInput</span><span class="p">(</span><span class="s1">&#39;mck_password_form&#39;</span><span class="p">,</span> <span class="mi">1</span><span class="p">)</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC879'>				<span class="nx">parse</span><span class="p">(</span><span class="nx">EvalElse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">,</span> <span class="k">true</span><span class="p">))</span><span class="o">.</span><span class="nx">n</span><span class="o">.</span></div><div class='line' id='LC880'>				<span class="nx">callback_event</span><span class="p">(</span><span class="s1">&#39;mck_login.password_form&#39;</span><span class="p">)</span><span class="o">.</span></div><div class='line' id='LC881'>			<span class="s1">&#39;&lt;/form&gt;&#39;</span><span class="p">;</span></div><div class='line' id='LC882'><br/></div><div class='line' id='LC883'>		<span class="nx">mck_login_errors</span><span class="p">(</span><span class="k">null</span><span class="p">);</span></div><div class='line' id='LC884'><br/></div><div class='line' id='LC885'>		<span class="k">return</span> <span class="nv">$thing</span><span class="p">;</span></div><div class='line' id='LC886'>	<span class="p">}</span></div><div class='line' id='LC887'><br/></div><div class='line' id='LC888'><span class="sd">/**</span></div><div class='line' id='LC889'><span class="sd"> * Generates HTML form inputs</span></div><div class='line' id='LC890'><span class="sd"> * @param array $atts Array of HTML input&#39;s attributes. i.e. array(&#39;type&#39; =&gt; &#39;password&#39;, ...)</span></div><div class='line' id='LC891'><span class="sd"> * @return string HTML markup</span></div><div class='line' id='LC892'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC893'><span class="sd"> *		&lt;txp:mck_login_input type=&quot;text&quot; name=&quot;foo&quot; value=&quot;bar&quot; /&gt;</span></div><div class='line' id='LC894'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC895'><span class="sd"> */</span></div><div class='line' id='LC896'><br/></div><div class='line' id='LC897'>	<span class="k">function</span> <span class="nf">mck_login_input</span><span class="p">(</span><span class="nv">$atts</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC898'><br/></div><div class='line' id='LC899'>		<span class="k">static</span> <span class="nv">$uid</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span></div><div class='line' id='LC900'><br/></div><div class='line' id='LC901'>		<span class="nv">$r</span> <span class="o">=</span> <span class="nx">lAtts</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC902'>			<span class="s1">&#39;type&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;text&#39;</span><span class="p">,</span></div><div class='line' id='LC903'>			<span class="s1">&#39;name&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;&#39;</span><span class="p">,</span></div><div class='line' id='LC904'>			<span class="s1">&#39;value&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;&#39;</span><span class="p">,</span></div><div class='line' id='LC905'>			<span class="s1">&#39;class&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;mck_login_input&#39;</span><span class="p">,</span></div><div class='line' id='LC906'>			<span class="s1">&#39;id&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;&#39;</span><span class="p">,</span></div><div class='line' id='LC907'>			<span class="s1">&#39;label&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;&#39;</span><span class="p">,</span></div><div class='line' id='LC908'>			<span class="s1">&#39;required&#39;</span> <span class="o">=&gt;</span> <span class="mi">1</span><span class="p">,</span></div><div class='line' id='LC909'>			<span class="s1">&#39;remember&#39;</span> <span class="o">=&gt;</span> <span class="mi">1</span><span class="p">,</span></div><div class='line' id='LC910'>		<span class="p">),</span> <span class="nv">$atts</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span></div><div class='line' id='LC911'><br/></div><div class='line' id='LC912'>		<span class="nb">extract</span><span class="p">(</span><span class="nv">$r</span><span class="p">);</span></div><div class='line' id='LC913'><br/></div><div class='line' id='LC914'>		<span class="k">if</span><span class="p">(</span><span class="nv">$type</span> <span class="o">==</span> <span class="s1">&#39;token&#39;</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC915'>			<span class="k">return</span> <span class="nx">hInput</span><span class="p">(</span><span class="s1">&#39;mck_login_token&#39;</span><span class="p">,</span> <span class="nx">mck_login_token</span><span class="p">());</span></div><div class='line' id='LC916'>		<span class="p">}</span></div><div class='line' id='LC917'><br/></div><div class='line' id='LC918'>		<span class="k">if</span><span class="p">(</span><span class="nv">$required</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC919'>			<span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;class&#39;</span><span class="p">]</span> <span class="o">.=</span> <span class="s1">&#39; mck_login_required&#39;</span><span class="p">;</span></div><div class='line' id='LC920'>		<span class="p">}</span></div><div class='line' id='LC921'><br/></div><div class='line' id='LC922'>		<span class="k">if</span><span class="p">(</span><span class="nb">isset</span><span class="p">(</span><span class="nv">$_POST</span><span class="p">[</span><span class="nv">$name</span><span class="p">]))</span> <span class="p">{</span></div><div class='line' id='LC923'><br/></div><div class='line' id='LC924'>			<span class="k">if</span><span class="p">(</span><span class="nv">$type</span> <span class="o">==</span> <span class="s1">&#39;checkbox&#39;</span> <span class="o">&amp;&amp;</span> <span class="nx">ps</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span> <span class="o">==</span> <span class="nv">$value</span><span class="p">)</span></div><div class='line' id='LC925'>				<span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;checked&#39;</span><span class="p">]</span> <span class="o">=</span> <span class="s1">&#39;checked&#39;</span><span class="p">;</span></div><div class='line' id='LC926'><br/></div><div class='line' id='LC927'>			<span class="k">if</span><span class="p">(</span><span class="nv">$type</span> <span class="o">!=</span> <span class="s1">&#39;password&#39;</span> <span class="o">&amp;&amp;</span> <span class="nv">$remember</span><span class="p">)</span></div><div class='line' id='LC928'>				<span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;value&#39;</span><span class="p">]</span> <span class="o">=</span> <span class="nx">ps</span><span class="p">(</span><span class="nv">$name</span><span class="p">);</span></div><div class='line' id='LC929'><br/></div><div class='line' id='LC930'>			<span class="k">if</span><span class="p">(</span><span class="nx">ps</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span> <span class="o">===</span> <span class="s1">&#39;&#39;</span> <span class="o">&amp;&amp;</span> <span class="nv">$required</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC931'>				<span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;class&#39;</span><span class="p">]</span> <span class="o">.=</span> <span class="s1">&#39; mck_login_error&#39;</span><span class="p">;</span></div><div class='line' id='LC932'>			<span class="p">}</span></div><div class='line' id='LC933'>		<span class="p">}</span></div><div class='line' id='LC934'><br/></div><div class='line' id='LC935'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$id</span> <span class="o">&amp;&amp;</span> <span class="nv">$uid</span><span class="o">++</span><span class="p">)</span></div><div class='line' id='LC936'>			<span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;id&#39;</span><span class="p">]</span> <span class="o">=</span> <span class="s1">&#39;mck_login_&#39;</span> <span class="o">.</span> <span class="nb">md5</span><span class="p">(</span><span class="nv">$name</span> <span class="o">.</span> <span class="nv">$uid</span><span class="p">);</span></div><div class='line' id='LC937'><br/></div><div class='line' id='LC938'>		<span class="k">if</span><span class="p">(</span><span class="nv">$label</span><span class="p">)</span></div><div class='line' id='LC939'>			<span class="nv">$label</span> <span class="o">=</span> <span class="s1">&#39;&lt;label for=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;id&#39;</span><span class="p">])</span><span class="o">.</span><span class="s1">&#39;&quot;&gt;&#39;</span><span class="o">.</span></div><div class='line' id='LC940'>				<span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;label&#39;</span><span class="p">])</span><span class="o">.</span><span class="s1">&#39;&lt;/label&gt;&#39;</span><span class="o">.</span><span class="nx">n</span><span class="p">;</span></div><div class='line' id='LC941'><br/></div><div class='line' id='LC942'>		<span class="nv">$r</span> <span class="o">=</span> <span class="nb">array_merge</span><span class="p">((</span><span class="k">array</span><span class="p">)</span> <span class="nv">$atts</span><span class="p">,</span> <span class="p">(</span><span class="k">array</span><span class="p">)</span> <span class="nv">$r</span><span class="p">);</span></div><div class='line' id='LC943'>		<span class="nb">unset</span><span class="p">(</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;label&#39;</span><span class="p">]);</span></div><div class='line' id='LC944'><br/></div><div class='line' id='LC945'>		<span class="k">if</span><span class="p">(</span><span class="nv">$required</span> <span class="o">!=</span> <span class="s1">&#39;required&#39;</span><span class="p">)</span></div><div class='line' id='LC946'>			<span class="nb">unset</span><span class="p">(</span><span class="nv">$r</span><span class="p">[</span><span class="s1">&#39;required&#39;</span><span class="p">]);</span></div><div class='line' id='LC947'><br/></div><div class='line' id='LC948'>		<span class="nv">$out</span> <span class="o">=</span> <span class="k">array</span><span class="p">();</span></div><div class='line' id='LC949'><br/></div><div class='line' id='LC950'>		<span class="k">foreach</span><span class="p">(</span><span class="nv">$r</span> <span class="k">as</span> <span class="nv">$name</span> <span class="o">=&gt;</span> <span class="nv">$value</span><span class="p">)</span></div><div class='line' id='LC951'>			<span class="k">if</span><span class="p">(</span><span class="nv">$value</span> <span class="o">!==</span> <span class="s1">&#39;&#39;</span> <span class="o">||</span> <span class="nv">$name</span> <span class="o">==</span> <span class="s1">&#39;value&#39;</span><span class="p">)</span></div><div class='line' id='LC952'>				<span class="nv">$out</span><span class="p">[]</span> <span class="o">=</span> <span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$name</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;=&quot;&#39;</span><span class="o">.</span><span class="nb">htmlspecialchars</span><span class="p">(</span><span class="nv">$value</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot;&#39;</span><span class="p">;</span></div><div class='line' id='LC953'><br/></div><div class='line' id='LC954'>		<span class="k">return</span> <span class="nv">$label</span> <span class="o">.</span> <span class="s1">&#39;&lt;input &#39;</span><span class="o">.</span> <span class="nb">implode</span><span class="p">(</span><span class="s1">&#39; &#39;</span><span class="p">,</span> <span class="nv">$out</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39; /&gt;&#39;</span><span class="p">;</span></div><div class='line' id='LC955'>	<span class="p">}</span></div><div class='line' id='LC956'><br/></div><div class='line' id='LC957'><span class="sd">/**</span></div><div class='line' id='LC958'><span class="sd"> * Displays error messages</span></div><div class='line' id='LC959'><span class="sd"> * @param array|string $atts</span></div><div class='line' id='LC960'><span class="sd"> * @param string $atts[for] Sets which form&#39;s errors are shown. Either login, reset, password, register.</span></div><div class='line' id='LC961'><span class="sd"> * @param string $atts[wraptag] HTML wraptag.</span></div><div class='line' id='LC962'><span class="sd"> * @param string $atts[break] HTML tag used to separate the items.</span></div><div class='line' id='LC963'><span class="sd"> * @param string $atts[class] Wraptag&#39;s HTML class.</span></div><div class='line' id='LC964'><span class="sd"> * @param int $atts[offset] Skip number of errors from the beginning.</span></div><div class='line' id='LC965'><span class="sd"> * @param int $atts[limit] Limit number of shown errors.</span></div><div class='line' id='LC966'><span class="sd"> * @return string HTML markup</span></div><div class='line' id='LC967'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC968'><span class="sd"> *		&lt;txp:mck_login_errors for=&quot;reset&quot; wraptag=&quot;p&quot; break=&quot;&quot; /&gt;</span></div><div class='line' id='LC969'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC970'><span class="sd"> */</span></div><div class='line' id='LC971'><br/></div><div class='line' id='LC972'>	<span class="k">function</span> <span class="nf">mck_login_errors</span><span class="p">(</span><span class="nv">$atts</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC973'><br/></div><div class='line' id='LC974'>		<span class="k">static</span> <span class="nv">$parent</span> <span class="o">=</span> <span class="k">NULL</span><span class="p">;</span></div><div class='line' id='LC975'><br/></div><div class='line' id='LC976'>		<span class="k">if</span><span class="p">(</span><span class="nb">is_string</span><span class="p">(</span><span class="nv">$atts</span><span class="p">)</span> <span class="o">||</span> <span class="nv">$atts</span> <span class="o">===</span> <span class="k">NULL</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC977'>			<span class="nv">$parent</span> <span class="o">=</span> <span class="nv">$atts</span><span class="p">;</span></div><div class='line' id='LC978'>			<span class="nx">mck_login</span><span class="o">::</span><span class="nv">$action</span> <span class="o">=</span> <span class="nv">$atts</span><span class="p">;</span></div><div class='line' id='LC979'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC980'>		<span class="p">}</span></div><div class='line' id='LC981'><br/></div><div class='line' id='LC982'>		<span class="nb">extract</span><span class="p">(</span><span class="nx">lAtts</span><span class="p">(</span><span class="k">array</span><span class="p">(</span></div><div class='line' id='LC983'>			<span class="s1">&#39;for&#39;</span> <span class="o">=&gt;</span> <span class="nv">$parent</span><span class="p">,</span></div><div class='line' id='LC984'>			<span class="s1">&#39;wraptag&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;ul&#39;</span><span class="p">,</span></div><div class='line' id='LC985'>			<span class="s1">&#39;break&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;li&#39;</span><span class="p">,</span></div><div class='line' id='LC986'>			<span class="s1">&#39;class&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;&#39;</span><span class="p">,</span></div><div class='line' id='LC987'>			<span class="s1">&#39;offset&#39;</span> <span class="o">=&gt;</span> <span class="mi">0</span><span class="p">,</span></div><div class='line' id='LC988'>			<span class="s1">&#39;limit&#39;</span> <span class="o">=&gt;</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC989'>		<span class="p">),</span> <span class="nv">$atts</span><span class="p">));</span></div><div class='line' id='LC990'><br/></div><div class='line' id='LC991'>		<span class="nv">$r</span> <span class="o">=</span> <span class="nx">mck_login</span><span class="o">::</span><span class="na">error</span><span class="p">();</span></div><div class='line' id='LC992'><br/></div><div class='line' id='LC993'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$r</span><span class="p">)</span></div><div class='line' id='LC994'>			<span class="k">return</span><span class="p">;</span></div><div class='line' id='LC995'><br/></div><div class='line' id='LC996'>		<span class="k">if</span><span class="p">(</span><span class="nv">$offset</span> <span class="o">||</span> <span class="nv">$limit</span><span class="p">)</span></div><div class='line' id='LC997'>			<span class="nv">$r</span> <span class="o">=</span> <span class="nb">array_slice</span><span class="p">(</span><span class="nv">$r</span><span class="p">,</span> <span class="nv">$offset</span><span class="p">,</span> <span class="nv">$limit</span><span class="p">);</span></div><div class='line' id='LC998'><br/></div><div class='line' id='LC999'>		<span class="nv">$out</span> <span class="o">=</span> <span class="k">array</span><span class="p">();</span></div><div class='line' id='LC1000'><br/></div><div class='line' id='LC1001'>		<span class="k">foreach</span><span class="p">(</span><span class="nv">$r</span> <span class="k">as</span> <span class="nv">$msg</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC1002'>			<span class="nv">$pfx</span> <span class="o">=</span> <span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;mck_login_&#39;</span><span class="o">.</span><span class="nv">$msg</span><span class="p">);</span></div><div class='line' id='LC1003'><br/></div><div class='line' id='LC1004'>			<span class="nv">$out</span><span class="p">[]</span> <span class="o">=</span> </div><div class='line' id='LC1005'>				<span class="s1">&#39;&lt;span class=&quot;mck_login_error_&#39;</span><span class="o">.</span><span class="nb">md5</span><span class="p">(</span><span class="nv">$msg</span><span class="p">)</span><span class="o">.</span><span class="s1">&#39;&quot;&gt;&#39;</span><span class="o">.</span></div><div class='line' id='LC1006'>					<span class="p">(</span><span class="nv">$pfx</span> <span class="o">==</span> <span class="s1">&#39;mck_login_&#39;</span> <span class="o">.</span> <span class="nv">$msg</span>  <span class="o">?</span> <span class="nx">gTxt</span><span class="p">(</span><span class="nv">$msg</span><span class="p">)</span> <span class="o">:</span> <span class="nv">$pfx</span><span class="p">)</span><span class="o">.</span></div><div class='line' id='LC1007'>				<span class="s1">&#39;&lt;/span&gt;&#39;</span><span class="p">;</span></div><div class='line' id='LC1008'>		<span class="p">}</span></div><div class='line' id='LC1009'><br/></div><div class='line' id='LC1010'>		<span class="k">return</span> <span class="nv">$out</span> <span class="o">?</span> <span class="nx">doWrap</span><span class="p">(</span><span class="nv">$out</span><span class="p">,</span> <span class="nv">$wraptag</span><span class="p">,</span> <span class="nv">$break</span><span class="p">,</span> <span class="nv">$class</span><span class="p">)</span> <span class="o">:</span> <span class="s1">&#39;&#39;</span><span class="p">;</span></div><div class='line' id='LC1011'>	<span class="p">}</span></div><div class='line' id='LC1012'><br/></div><div class='line' id='LC1013'><span class="sd">/**</span></div><div class='line' id='LC1014'><span class="sd"> * Generate a ciphered token.</span></div><div class='line' id='LC1015'><span class="sd"> * @return string</span></div><div class='line' id='LC1016'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC1017'><span class="sd"> *		&lt;txp:mck_login_token /&gt;</span></div><div class='line' id='LC1018'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC1019'><span class="sd"> */</span></div><div class='line' id='LC1020'><br/></div><div class='line' id='LC1021'>	<span class="k">function</span> <span class="nf">mck_login_token</span><span class="p">()</span> <span class="p">{</span></div><div class='line' id='LC1022'><br/></div><div class='line' id='LC1023'>		<span class="k">static</span> <span class="nv">$token</span><span class="p">;</span></div><div class='line' id='LC1024'><br/></div><div class='line' id='LC1025'>		<span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="nv">$token</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC1026'><br/></div><div class='line' id='LC1027'>			<span class="nv">$nonce</span> <span class="o">=</span> </div><div class='line' id='LC1028'>				<span class="nx">fetch</span><span class="p">(</span></div><div class='line' id='LC1029'>					<span class="s1">&#39;nonce&#39;</span><span class="p">,</span> <span class="s1">&#39;txp_users&#39;</span><span class="p">,</span> <span class="s1">&#39;name&#39;</span><span class="p">,</span> </div><div class='line' id='LC1030'>					<span class="nx">mck_login</span><span class="p">(</span><span class="k">array</span><span class="p">(</span><span class="s1">&#39;name&#39;</span> <span class="o">=&gt;</span> <span class="s1">&#39;name&#39;</span><span class="p">))</span></div><div class='line' id='LC1031'>				<span class="p">);</span></div><div class='line' id='LC1032'><br/></div><div class='line' id='LC1033'>			<span class="nv">$token</span> <span class="o">=</span> <span class="nb">md5</span><span class="p">(</span><span class="nv">$nonce</span> <span class="o">.</span> <span class="nx">get_pref</span><span class="p">(</span><span class="s1">&#39;blog_uid&#39;</span><span class="p">));</span></div><div class='line' id='LC1034'>		<span class="p">}</span></div><div class='line' id='LC1035'><br/></div><div class='line' id='LC1036'>		<span class="k">return</span> <span class="nv">$token</span><span class="p">;</span></div><div class='line' id='LC1037'>	<span class="p">}</span></div><div class='line' id='LC1038'><br/></div><div class='line' id='LC1039'><span class="sd">/**</span></div><div class='line' id='LC1040'><span class="sd"> * Bouncer. Checks token, and protects against CSRF attempts.</span></div><div class='line' id='LC1041'><span class="sd"> * @param mixed $void</span></div><div class='line' id='LC1042'><span class="sd"> * @param string $thing</span></div><div class='line' id='LC1043'><span class="sd"> * @return mixed</span></div><div class='line' id='LC1044'><span class="sd"> * &lt;code&gt;</span></div><div class='line' id='LC1045'><span class="sd"> *		&lt;txp:mck_login_bouncer /&gt;</span></div><div class='line' id='LC1046'><span class="sd"> * &lt;/code&gt;</span></div><div class='line' id='LC1047'><span class="sd"> */</span></div><div class='line' id='LC1048'><br/></div><div class='line' id='LC1049'>	<span class="k">function</span> <span class="nf">mck_login_bouncer</span><span class="p">(</span><span class="nv">$void</span><span class="o">=</span><span class="k">NULL</span><span class="p">,</span> <span class="nv">$thing</span><span class="o">=</span><span class="k">NULL</span><span class="p">)</span> <span class="p">{</span></div><div class='line' id='LC1050'>		<span class="k">if</span><span class="p">(</span><span class="nx">gps</span><span class="p">(</span><span class="s1">&#39;mck_login_token&#39;</span><span class="p">)</span> <span class="o">!=</span> <span class="nx">mck_login_token</span><span class="p">())</span> <span class="p">{</span></div><div class='line' id='LC1051'><br/></div><div class='line' id='LC1052'>			<span class="nb">sleep</span><span class="p">(</span><span class="mi">3</span><span class="p">);</span></div><div class='line' id='LC1053'><br/></div><div class='line' id='LC1054'>			<span class="k">if</span><span class="p">(</span><span class="nv">$thing</span> <span class="o">!==</span> <span class="k">NULL</span><span class="p">)</span></div><div class='line' id='LC1055'>				<span class="k">return</span> <span class="k">false</span><span class="p">;</span></div><div class='line' id='LC1056'><br/></div><div class='line' id='LC1057'>			<span class="nx">txp_die</span><span class="p">(</span><span class="nx">gTxt</span><span class="p">(</span><span class="s1">&#39;mck_login_invalid_csrf_token&#39;</span><span class="p">),</span> <span class="s1">&#39;401&#39;</span><span class="p">);</span></div><div class='line' id='LC1058'>		<span class="p">}</span></div><div class='line' id='LC1059'><br/></div><div class='line' id='LC1060'>		<span class="k">if</span><span class="p">(</span><span class="nv">$thing</span> <span class="o">!==</span> <span class="k">NULL</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="nv">$void</span><span class="p">)</span></div><div class='line' id='LC1061'>			<span class="k">return</span> <span class="nx">parse</span><span class="p">(</span><span class="nv">$thing</span><span class="p">);</span></div><div class='line' id='LC1062'>	<span class="p">}</span></div><div class='line' id='LC1063'><br/></div><div class='line' id='LC1064'><span class="cp">?&gt;</span><span class="x"></span></div></pre></div>
          </td>
        </tr>
      </table>
  </div>

          </div>
        </div>

        <a href="#jump-to-line" rel="facebox" data-hotkey="l" class="js-jump-to-line" style="display:none">Jump to Line</a>
        <div id="jump-to-line" style="display:none">
          <h2>Jump to Line</h2>
          <form accept-charset="UTF-8" class="js-jump-to-line-form">
            <input class="textfield js-jump-to-line-field" type="text">
            <div class="full-button">
              <button type="submit" class="button">Go</button>
            </div>
          </form>
        </div>

      </div>
    </div>
</div>

<div id="js-frame-loading-template" class="frame frame-loading large-loading-area" style="display:none;">
  <img class="js-frame-loading-spinner" src="https://a248.e.akamai.net/assets.github.com/images/spinners/octocat-spinner-128.gif?1360648843" height="64" width="64">
</div>


        </div>
      </div>
      <div class="modal-backdrop"></div>
    </div>

      <div id="footer-push"></div><!-- hack for sticky footer -->
    </div><!-- end of wrapper - hack for sticky footer -->

      <!-- footer -->
      <div id="footer">
  <div class="container clearfix">

      <dl class="footer_nav">
        <dt>GitHub</dt>
        <dd><a href="https://github.com/about">About us</a></dd>
        <dd><a href="https://github.com/blog">Blog</a></dd>
        <dd><a href="https://github.com/contact">Contact &amp; support</a></dd>
        <dd><a href="http://enterprise.github.com/">GitHub Enterprise</a></dd>
        <dd><a href="http://status.github.com/">Site status</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Applications</dt>
        <dd><a href="http://mac.github.com/">GitHub for Mac</a></dd>
        <dd><a href="http://windows.github.com/">GitHub for Windows</a></dd>
        <dd><a href="http://eclipse.github.com/">GitHub for Eclipse</a></dd>
        <dd><a href="http://mobile.github.com/">GitHub mobile apps</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Services</dt>
        <dd><a href="http://get.gaug.es/">Gauges: Web analytics</a></dd>
        <dd><a href="http://speakerdeck.com">Speaker Deck: Presentations</a></dd>
        <dd><a href="https://gist.github.com">Gist: Code snippets</a></dd>
        <dd><a href="http://jobs.github.com/">Job board</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Documentation</dt>
        <dd><a href="http://help.github.com/">GitHub Help</a></dd>
        <dd><a href="http://developer.github.com/">Developer API</a></dd>
        <dd><a href="http://github.github.com/github-flavored-markdown/">GitHub Flavored Markdown</a></dd>
        <dd><a href="http://pages.github.com/">GitHub Pages</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>More</dt>
        <dd><a href="http://training.github.com/">Training</a></dd>
        <dd><a href="https://github.com/edu">Students &amp; teachers</a></dd>
        <dd><a href="http://shop.github.com">The Shop</a></dd>
        <dd><a href="/plans">Plans &amp; pricing</a></dd>
        <dd><a href="http://octodex.github.com/">The Octodex</a></dd>
      </dl>

      <hr class="footer-divider">


    <p class="right">&copy; 2013 <span title="0.23039s from fe3.rs.github.com">GitHub</span>, Inc. All rights reserved.</p>
    <a class="left" href="https://github.com/">
      <span class="mega-icon mega-icon-invertocat"></span>
    </a>
    <ul id="legal">
        <li><a href="https://github.com/site/terms">Terms of Service</a></li>
        <li><a href="https://github.com/site/privacy">Privacy</a></li>
        <li><a href="https://github.com/security">Security</a></li>
    </ul>

  </div><!-- /.container -->

</div><!-- /.#footer -->


    <div class="fullscreen-overlay js-fullscreen-overlay" id="fullscreen_overlay">
  <div class="fullscreen-container js-fullscreen-container">
    <div class="textarea-wrap">
      <textarea name="fullscreen-contents" id="fullscreen-contents" class="js-fullscreen-contents" placeholder="" data-suggester="fullscreen_suggester"></textarea>
          <div class="suggester-container">
              <div class="suggester fullscreen-suggester js-navigation-container" id="fullscreen_suggester"
                 data-url="/gocom/mck_login/suggestions/commit">
              </div>
          </div>
    </div>
  </div>
  <div class="fullscreen-sidebar">
    <a href="#" class="exit-fullscreen js-exit-fullscreen tooltipped leftwards" title="Exit Zen Mode">
      <span class="mega-icon mega-icon-normalscreen"></span>
    </a>
    <a href="#" class="theme-switcher js-theme-switcher tooltipped leftwards"
      title="Switch themes">
      <span class="mini-icon mini-icon-brightness"></span>
    </a>
  </div>
</div>



    <div id="ajax-error-message" class="flash flash-error">
      <span class="mini-icon mini-icon-exclamation"></span>
      Something went wrong with that request. Please try again.
      <a href="#" class="mini-icon mini-icon-remove-close ajax-error-dismiss"></a>
    </div>

    
    
    <span id='server_response_time' data-time='0.23082' data-host='fe3'></span>
    
  </body>
</html>

