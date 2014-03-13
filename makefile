deploy:
	hexo generate
	cp -r public ~/code/gh-pages/note
	cd ~/code/gh-pages/note && git add -u && git push origin gh-pages
