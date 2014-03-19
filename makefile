deploy:
	@hexo generate
	@cp -r  public/* ../gh-pages/note
	@cd ../gh-pages/note && git add . && git add -u && \
	 git commit -m 'add articles' && git push origin gh-pages
